<?php namespace modules\project;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\components\CommentRelation;
use modules\calendar\components\EventRelation;
use modules\core\base\Module;
use modules\note\components\NoteRelation;
use modules\project\components\Hook;
use modules\project\components\ProjectDiscussionTopicCommentRelation;
use modules\project\components\ProjectEventRelation;
use modules\project\components\ProjectNoteRelation;
use modules\project\components\ProjectQuickSearch;
use modules\project\components\ProjectTaskRelation;
use modules\quick_access\components\QuickSearch;
use modules\task\components\TaskRelation;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Project extends Module
{
    public function init()
    {
        parent::init();

        Hook::instance();

        TaskRelation::register('project', ProjectTaskRelation::class);
        NoteRelation::register('project', ProjectNoteRelation::class);

        if (Yii::$app->hasModule('calendar')) {
            EventRelation::register('project', ProjectEventRelation::class);
        }


        if (Yii::$app->hasModule('quick_access')) {
            QuickSearch::register(ProjectQuickSearch::class);
        }

        CommentRelation::register('project_discussion_topic', ProjectDiscussionTopicCommentRelation::class);
    }
}
