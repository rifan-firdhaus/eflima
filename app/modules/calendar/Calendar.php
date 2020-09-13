<?php namespace modules\calendar;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\components\CommentRelation;
use modules\calendar\components\EventCommentRelation;
use modules\calendar\components\EventNoteRelation;
use modules\calendar\components\Hook;
use modules\core\base\Module;
use modules\note\components\NoteRelation;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Calendar extends Module
{
    public function init()
    {
        parent::init();

        Hook::instance();

        NoteRelation::register('event', EventNoteRelation::class);
        CommentRelation::register('event', EventCommentRelation::class);
    }
}