<?php namespace modules\support\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\note\components\NoteRelation;
use modules\support\models\Ticket;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Ticket $model
 */
class TicketNoteRelation extends NoteRelation
{
    use TicketRelatedTrait;
}