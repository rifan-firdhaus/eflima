<?php namespace modules\task;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\base\Module;
use modules\note\components\NoteRelation;
use modules\quick_access\components\QuickSearch;
use modules\task\components\Hook;
use modules\task\components\TaskNoteRelation;
use modules\task\components\TaskQuickSearch;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Task extends Module
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Hook::instance();

        if (Yii::$app->hasModule('quick_access')) {
            QuickSearch::register(TaskQuickSearch::class);
        }

        NoteRelation::register('task', TaskNoteRelation::class);
    }
}