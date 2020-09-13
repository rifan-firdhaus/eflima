<?php namespace modules\address\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\address\models\City;
use modules\ui\widgets\inputs\Select2Input;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CityInput extends Select2Input
{
    public $is_enabled;
    public $country_code;
    public $province_code;

    public $provinceCodeAttribute;

    public $url = ['/address/admin/city/auto-complete'];

    /** @inheritdoc */
    public function normalize()
    {
        $params = Json::encode([
            'q' => new JsExpression('params.term'),
            'page' => new JsExpression('params.page'),
            'province_code' => ($this->provinceCodeAttribute ? new JsExpression("$('#{$this->provinceCodeAttribute}').val()") : null),
        ]);

        $this->jsOptions['ajax'] = [
            'url' => Url::to($this->buildUrl()),
            'data' => new JsExpression(
            /** @lang JavaScript */
                "function (params) {
                    return {$params};
                }"
            ),
        ];

        $this->jsOptions['templateResult'] = new JsExpression(
        /** @lang JavaScript */
            "function(data){
                if(data){
                    var _state = $('<div><div class=\'select2-option-primary-text\'></div><div class=\'select2-option-secondary-text text-muted\'></div></div>');
                    var province = data.province_name ? data.province_name : $(data.element).data('province-name');
                    
                    if(province){
                        _state.find('.select2-option-secondary-text').text(province);
                    }else{
                        _state.find('.select2-option-secondary-text').addClass('d-none');
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

                    return City::find()->andWhere(['id' => $value])->all();
                }

                $model = City::find()->andWhere(['id' => $value])->one();

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
        $queryableAttributes = ['is_enabled', 'country_code', 'province_code'];

        foreach ($queryableAttributes AS $attribute) {
            if (!is_null($this->{$attribute}) && $this->{$attribute} !== '') {
                $url[$attribute] = $this->{$attribute};
            }
        }

        return $url;
    }
}