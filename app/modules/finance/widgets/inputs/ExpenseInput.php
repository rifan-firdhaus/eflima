<?php namespace modules\finance\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\Expense;
use modules\ui\widgets\inputs\Select2Input;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ExpenseInput extends Select2Input
{
    public $status_id;
    public $customer_id;
    public $is_paid;
    public $url = ['/finance/admin/expense/auto-complete'];

    /**
     * @return array
     */
    public function buildUrl()
    {
        $url = $this->url;
        $queryableAttributes = ['status_id','customer_id','is_paid'];

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
        $this->jsOptions['ajax'] = [
            'url' => Url::to($this->buildUrl()),
            'data' => new JsExpression("function (params) {return {'q': params.term,page: params.page};}"),
        ];

        $this->jsOptions['templateResult'] = new JsExpression(
        /** @lang JavaScript */
            "function(data){
                if(data && typeof data.customer_name !== 'undefined'){
                    var state = $('<div class=\"align-middle\">'+data.text+'</div><div class=\"text-muted select2-text-secondary font-size-sm\">'+data.customer_name+'</div>');
                    
                    if(!data.customer_name){
                      state.filter('.select2-text-secondary').hide();
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

                    return Expense::find()->andWhere(['id' => $value])->map('id', 'name');
                }

                $model = Expense::find()->andWhere(['id' => $value])->one();

                if ($model) {
                    return [$value => $model->name];
                }
            };
        }

        parent::normalize();
    }
}