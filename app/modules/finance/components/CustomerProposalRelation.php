<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\crm\components\CustomerRelatedTrait;
use modules\crm\models\Customer;
use modules\crm\widgets\inputs\CustomerInput;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property-read Customer $model
 */
class CustomerProposalRelation extends ProposalRelation
{
    use CustomerRelatedTrait;

    public $useDefaultPicker = false;

    /**
     * @inheritDoc
     */
    public function pickerInput($proposal, $attribute)
    {
        return CustomerInput::widget([
            'model' => $proposal,
            'attribute' => $attribute,
            'prompt' => '',
            'jsOptions' => [
                'allowClear' => true,
                'width' => '100%',
            ],
        ]);
    }

    /**
     * @param Customer $model
     *
     * @inheritDoc
     */
    public function getAddress($model)
    {
        return [
            'city' => $model->city,
            'province' => $model->province,
            'country' => $model->country->name,
            'address' => $model->address,
            'postal_code' => $model->postal_code,
        ];
    }
}
