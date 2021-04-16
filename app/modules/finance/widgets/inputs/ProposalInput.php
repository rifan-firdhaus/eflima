<?php namespace modules\finance\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\Invoice;
use modules\finance\models\Proposal;
use modules\ui\widgets\inputs\Select2Input;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProposalInput extends Select2Input
{
    public $status_id;
    public $url = ['/finance/admin/proposal/auto-complete'];

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
        $this->jsOptions['ajax'] = [
            'url' => Url::to($this->buildUrl()),
            'data' => new JsExpression("function (params) {return {'q': params.term,page: params.page};}"),
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

                    return Proposal::find()->andWhere(['id' => $value])->map('id', 'title');
                }

                $model = Proposal::find()->andWhere(['id' => $value])->one();

                if ($model) {
                    return [$value => $model->title];
                }
            };
        }

        parent::normalize();
    }
}
