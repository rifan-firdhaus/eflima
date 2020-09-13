<?php namespace modules\crm\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\Staff;
use modules\ui\widgets\inputs\Select2Input;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CustomerContactInput extends Select2Input
{
    public $is_blocked;

    public $url = ['/crm/admin/customer-contact/auto-complete'];

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
                if(data){
                    var _state = $('<div class=\'d-flex\'><img class=\'rounded-circle mr-2\' width=\'45px\' height=\'45px\'/><div><div class=\'select2-option-primary-text\'></div><div class=\'select2-option-secondary-text text-muted\'></div></div></div>');
                    var avatar = data.avatar ? data.avatar : $(data.element).data('avatar');
                    var companyName = data.customer_company_name ? data.customer_company_name :  $(data.element).data('company_name');
                    
                    if(avatar){
                        _state.find('img').attr('src',avatar)
                    }else{
                        _state.find('img').addClass('d-none')
                    }
                    
                    if(companyName){
                        _state.find('.select2-option-secondary-text').text(companyName)
                    }else{
                        _state.find('.select2-option-secondary-text').addClass('d-none')
                    }
                    
                    _state.find('.select2-option-primary-text').text(data.text);
                    
                    return _state;
                }
                
                return data.text;
            }"
        );

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