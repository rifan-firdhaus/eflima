<?php

namespace modules\task\models\query;

use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\task\models\TaskPriority;

/**
 * This is the ActiveQuery class for [[\modules\task\models\TaskPriority]].
 *
 * @see \modules\task\models\TaskPriority
 */
class TaskPriorityQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     *
     * @return TaskPriority[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return TaskPriority|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
