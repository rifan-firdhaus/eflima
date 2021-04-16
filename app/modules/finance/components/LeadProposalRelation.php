<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\crm\components\LeadRelatedTrait;
use modules\crm\models\Lead;
use modules\crm\widgets\inputs\LeadInput;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property-read Lead $model
 */
class LeadProposalRelation extends ProposalRelation
{
    use LeadRelatedTrait;

    public $useDefaultPicker = false;

    /**
     * @inheritDoc
     */
    public function pickerInput($proposal, $attribute)
    {
        return LeadInput::widget([
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
     * @inheritDoc
     *
     * @param Lead $model
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
