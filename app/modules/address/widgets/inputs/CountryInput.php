<?php namespace modules\address\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\address\assets\FlagIconAsset;
use modules\address\models\Country;
use modules\ui\widgets\inputs\Select2Input;
use yii\helpers\Url;
use yii\web\JsExpression;
use function array_keys;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CountryInput extends Select2Input
{
    public $is_enabled;

    public $url = ['/address/admin/country/auto-complete'];

    public function registerAssets()
    {
        FlagIconAsset::register($this->view);

        parent::registerAssets();
    }

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
                    var state = $('<span class=\"flag-icon border mr-2 align-middle\" style=\"width:28px;height:23px\"></span><span class=\"align-middle\">'+data.text+'</span>');
                    var iso2 = data.iso2 ? data.iso2 : $(data.element).data('iso2');
                    
                    if(iso2){
                        state.filter('.flag-icon').addClass('flag-icon-'+iso2.toLowerCase());
                    }else{
                        state.filter('.flag-icon').addClass('d-none');
                    }
                    
                    return state;
                }
                
                return '';
            }"
        );
        $this->jsOptions['templateSelection'] = $this->jsOptions['templateResult'];

        if (!$this->selected) {
            $this->selected = function ($value, $select2) {
                if ($select2->multiple) {
                    is_array($value) || ($value = explode(',', $value));

                    return Country::find()->andWhere(['code' => $value])->all();
                }

                $model = Country::find()->andWhere(['code' => $value])->one();

                if ($model) {
                    return [$value => $model->name];
                }
            };
        }

        parent::normalize();

        $ids = array_keys($this->source);

        if ($ids) {
            $models = Country::find()->andWhere(['code' => $ids])->all();

            foreach ($models AS $model) {
                $this->options['options'][$model->code] = ['data-iso2' => $model->iso2];
            }
        }
    }

    /**
     * @return array
     */
    public function buildUrl()
    {
        $url = $this->url;
        $queryableAttributes = ['is_enabled'];

        foreach ($queryableAttributes AS $attribute) {
            if (!is_null($this->{$attribute}) && $this->{$attribute} !== '') {
                $url[$attribute] = $this->{$attribute};
            }
        }

        return $url;
    }
}