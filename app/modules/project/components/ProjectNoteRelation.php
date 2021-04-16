<?php namespace modules\project\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\note\components\NoteRelation;
use modules\project\models\Project;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Project $model
 */
class ProjectNoteRelation extends NoteRelation
{
    use ProjectRelatedTrait;

    /**
     * @inheritDoc
     */
    public function isActive($modelId = null)
    {
        return Yii::$app->user->can('admin.project.view.detail');
    }
}
