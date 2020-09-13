<?php namespace modules\support\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\support\models\TicketPredefinedReply;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\support\models\TicketPredefinedReply]].
 *
 * @see    TicketPredefinedReply
 */
class TicketPredefinedReplyQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     *
     * @return TicketPredefinedReply[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return TicketPredefinedReply|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
