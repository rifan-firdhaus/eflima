<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\Expense;
use modules\finance\widgets\inputs\ExpenseInput;
use modules\finance\widgets\inputs\ProposalInput;
use modules\finance\widgets\inputs\ProposalStatusInput;
use modules\project\components\ProjectRelatedTrait;
use modules\task\components\TaskRelation;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Expense $model
 */
class ProposalTaskRelation extends TaskRelation
{
    use ProposalRelatedTrait;

    public $useDefaultPicker = false;

    /**
     * @inheritDoc
     */
    public function pickerInput($task, $attribute)
    {
        return ProposalInput::widget([
            'model' => $task,
            'attribute' => $attribute,
        ]);
    }
}
