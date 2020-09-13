<?php namespace modules\support;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\components\CommentRelation;
use modules\core\base\Module;
use modules\note\components\NoteRelation;
use modules\support\components\Hook;
use modules\support\components\KnowledgeBaseCommentRelation;
use modules\support\components\TicketNoteRelation;
use modules\support\components\TicketTaskRelation;
use modules\task\components\TaskRelation;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Support extends Module
{
    public function init()
    {
        parent::init();

        Hook::instance();

        TaskRelation::register('ticket', TicketTaskRelation::class);
        NoteRelation::register('ticket', TicketNoteRelation::class);
        CommentRelation::register('knowledge_base', KnowledgeBaseCommentRelation::class);
    }
}