<?php namespace modules\project\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\project\models\Project;
use modules\project\widgets\inputs\ProjectInput;
use modules\task\components\TaskRelation;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Project $model
 */
class ProjectTaskRelation extends TaskRelation
{
    use ProjectRelatedTrait;

    public $useDefaultPicker = false;

    /**
     * @inheritDoc
     */
    public function pickerInput($task, $attribute)
    {
        return ProjectInput::widget([
            'model' => $task,
            'attribute' => $attribute,
        ]);
    }
}