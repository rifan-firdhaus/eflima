<?php namespace modules\task\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\note\components\NoteRelation;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskNoteRelation extends NoteRelation
{
    use TaskRelatedTrait;

    public function isActive($modelId = null)
    {
        return Yii::$app->user->can('admin.task.view.detail');
    }
}
