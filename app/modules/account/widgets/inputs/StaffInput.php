<?php namespace modules\account\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\Staff;
use modules\ui\widgets\inputs\Select2Input;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class StaffInput extends Select2Input
{
    public $is_blocked;

    public $url = ['/account/admin/staff/auto-complete'];

    /** @inheritdoc */
    public function normalize()
    {
        $this->jsOptions['ajax'] = [
            'url' => Url::to($this->buildUrl()),
            'data' => new JsExpression("function (params) {return {'q': params.term,page: params.page};}"),
        ];

        if (!$this->selected) {
            $this->selected = function ($value, $select2) {
                if ($select2->multiple) {
                    is_array($value) || ($value = explode(',', $value));

                    return Staff::find()->andWhere(['id' => $value])->map('id', 'name');
                }

                $model = Staff::find()->andWhere(['id' => $value])->one();

                if ($model) {
                    return [$value => $model->name];
                }
            };
        }

        parent::normalize();
    }

    /**
     * @return array
     */
    public function buildUrl()
    {
        $url = $this->url;
        $queryableAttributes = ['is_blocked'];

        foreach ($queryableAttributes AS $attribute) {
            if (!is_null($this->{$attribute}) && $this->{$attribute} !== '') {
                $url[$attribute] = $this->{$attribute};
            }
        }

        return $url;
    }
}