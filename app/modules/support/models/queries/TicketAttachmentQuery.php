<?php namespace modules\support\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\support\models\TicketAttachment;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\support\models\TicketAttachment]].
 *
 * @see    TicketAttachment
 */
class TicketAttachmentQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return TicketAttachment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return TicketAttachment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
