<?php namespace modules\support\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\support\models\Ticket;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\support\models\Ticket]].
 *
 * @see    Ticket
 */
class TicketQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return Ticket[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Ticket|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
