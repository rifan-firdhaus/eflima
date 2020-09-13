<?php

namespace modules\support\models\queries;

use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\support\models\TicketStatus;

/**
 * This is the ActiveQuery class for [[\modules\task\models\TicketStatus]].
 *
 * @see \modules\task\models\TicketStatus
 */
class TicketStatusQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     *
     * @return TicketStatus[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return TicketStatus|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
