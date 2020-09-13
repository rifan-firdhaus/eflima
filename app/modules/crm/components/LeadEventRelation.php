<?php namespace modules\crm\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\calendar\components\EventRelation;
use modules\crm\models\Lead;
use modules\crm\widgets\inputs\LeadInput;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property mixed $label
 * @property Lead  $model
 */
class LeadEventRelation extends EventRelation
{
    use LeadRelatedTrait;

    public $useDefaultPicker = false;

    /**
     * @inheritDoc
     */
    public function pickerInput($task, $attribute)
    {
        return LeadInput::widget([
            'model' => $task,
            'attribute' => $attribute,
            'prompt' => '',
            'jsOptions' => [
                'allowClear' => true,
                'width' => '100%',
            ],
        ]);
    }
}