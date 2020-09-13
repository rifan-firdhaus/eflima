<?php namespace modules\crm\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\calendar\components\EventRelation;
use modules\crm\widgets\inputs\CustomerInput;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CustomerEventRelation extends EventRelation
{
    use CustomerRelatedTrait;

    public $useDefaultPicker = false;

    /**
     * @inheritDoc
     */
    public function pickerInput($task, $attribute)
    {
        return CustomerInput::widget([
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