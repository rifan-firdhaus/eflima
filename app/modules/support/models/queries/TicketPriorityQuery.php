<?php

namespace modules\support\models\queries;

use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\support\models\TicketPriority;

/**
 * This is the ActiveQuery class for [[\modules\task\models\TicketPriority]].
 *
 * @see \modules\task\models\TicketPriority
 */
class TicketPriorityQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     *
     * @return TicketPriority[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return TicketPriority|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
