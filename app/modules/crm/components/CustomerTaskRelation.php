<?php namespace modules\crm\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\crm\models\Customer;
use modules\crm\widgets\inputs\CustomerInput;
use modules\support\components\TicketRelatedTrait;
use modules\task\components\TaskRelation;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property mixed    $label
 * @property Customer $model
 */
class CustomerTaskRelation extends TaskRelation
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