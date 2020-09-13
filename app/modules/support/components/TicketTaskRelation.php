<?php namespace modules\support\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\support\models\Ticket;
use modules\support\widgets\inputs\TicketInput;
use modules\task\components\TaskRelation;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Ticket $model
 */
class TicketTaskRelation extends TaskRelation
{
    use TicketRelatedTrait;

    public $useDefaultPicker = false;

    /**
     * @inheritDoc
     */
    public function pickerInput($task, $attribute)
    {
        return TicketInput::widget([
            'model' => $task,
            'attribute' => $attribute,
        ]);
    }

}