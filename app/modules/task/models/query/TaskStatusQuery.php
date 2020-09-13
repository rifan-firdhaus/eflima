<?php

namespace modules\task\models\query;

use modules\core\db\ActiveQuery;
use modules\core\models\traits\VisibilityQuery;
use modules\task\models\TaskStatus;

/**
 * This is the ActiveQuery class for [[\modules\task\models\TaskStatus]].
 *
 * @see \modules\task\models\TaskStatus
 */
class TaskStatusQuery extends ActiveQuery
{
    use VisibilityQuery;

    /**
     * @inheritdoc
     *
     * @return TaskStatus[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return TaskStatus|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
