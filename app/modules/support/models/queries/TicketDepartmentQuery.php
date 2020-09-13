<?php

namespace modules\support\models\queries;

use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\support\models\TicketDepartment;

/**
 * This is the ActiveQuery class for [[\modules\task\models\TicketDepartment]].
 *
 * @see \modules\task\models\TicketDepartment
 */
class TicketDepartmentQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     *
     * @return TicketDepartment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return TicketDepartment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
