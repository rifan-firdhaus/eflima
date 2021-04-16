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


        $this->jsOptions['templateResult'] = new JsExpression(
        /** @lang JavaScript */
            "function(data){
                if(data && data.name && data.avatar){
                    var state = $('<div class=\"d-flex align-items-center\"><img style=\"width:3rem\" class=\"rounded-circle mr-2\" src=\"'+data.avatar+'\"><div><div class=\"align-middle\">'+data.text+'</div><small class=\"text-muted\">'+data.name+'</small></div></div>');
                    
                    if(!data.name){
                      state.find('small').hide();
                    }
                    
                    return state;
                }
                
                return data.text;
            }"
        );

        if (!$this->selected) {
            $this->selected = function ($value, $select2) {
                if ($select2->multiple) {
                    is_array($value) || ($value = explode(',', $value));

                    return Staff::find()->andWhere(['staff.id' => $value])->joinWith('account')->map('id', 'account.username');
                }

                $model = Staff::find()->andWhere(['staff.id' => $value])->one();

                if ($model) {
                    return [$value => $model->account->username];
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
