<?php namespace modules\crm;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\components\CommentRelation;
use modules\calendar\components\EventRelation;
use modules\core\base\Module;
use modules\crm\components\CustomerEventRelation;
use modules\crm\components\CustomerNoteRelation;
use modules\crm\components\CustomerTaskRelation;
use modules\crm\components\Hook;
use modules\crm\components\LeadCommentRelation;
use modules\crm\components\LeadEventRelation;
use modules\crm\components\LeadNoteRelation;
use modules\crm\components\LeadTaskRelation;
use modules\note\components\NoteRelation;
use modules\task\components\TaskRelation;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CRM extends Module
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Hook::instance();

        if (Yii::$app->hasModule('task')) {
            TaskRelation::register('customer', CustomerTaskRelation::class);
            TaskRelation::register('lead', LeadTaskRelation::class);
        }

        NoteRelation::register('customer', CustomerNoteRelation::class);
        NoteRelation::register('lead', LeadNoteRelation::class);

        CommentRelation::register('lead', LeadCommentRelation::class);

        if (Yii::$app->hasModule('calendar')) {
            EventRelation::register('customer', CustomerEventRelation::class);
            EventRelation::register('lead', LeadEventRelation::class);
        }
    }
}