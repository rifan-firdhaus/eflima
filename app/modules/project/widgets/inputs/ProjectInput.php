<?php namespace modules\project\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\project\models\Project;
use modules\ui\widgets\inputs\Select2Input;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProjectInput extends Select2Input
{
    public $status_id;
    public $customerInputSelector;
    public $url = ['/project/admin/project/auto-complete'];

    /**
     * @return array
     */
    public function buildUrl()
    {
        $url = $this->url;
        $queryableAttributes = ['status_id'];

        foreach ($queryableAttributes AS $attribute) {
            if (!is_null($this->{$attribute}) && $this->{$attribute} !== '') {
                $url[$attribute] = $this->{$attribute};
            }
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    public function normalize()
    {
        $params = [
            'q' => new JsExpression('params.term'),
            'page' => new JsExpression('params.page'),
        ];

        if (isset($this->customerInputSelector)) {
            $params['customer_id'] = new JsExpression("$('{$this->customerInputSelector}').val()");
        }

        $params = Json::encode($params);

        $this->jsOptions['ajax'] = [
            'url' => Url::to($this->buildUrl()),
            'data' => new JsExpression("function (params) {return {$params};}"),
        ];

        $this->jsOptions['templateResult'] = new JsExpression(
        /** @lang JavaScript */
            "function(data){
                if(data && data.customer_name){
                    var state = $('<div class=\"align-middle\">'+data.text+'</div><div class=\"text-muted font-size-sm\">'+data.customer_name+'</div>');
                    
                    return state;
                }
                
                return data.text;
            }"
        );

        if (!$this->selected) {
            $this->selected = function ($value, $select2) {
                if ($select2->multiple) {
                    is_array($value) || ($value = explode(',', $value));

                    return Project::find()->andWhere(['id' => $value])->map('id', 'name');
                }

                $model = Project::find()->andWhere(['id' => $value])->one();

                if ($model) {
                    return [$value => $model->name];
                }
            };
        }

        parent::normalize();
    }
}