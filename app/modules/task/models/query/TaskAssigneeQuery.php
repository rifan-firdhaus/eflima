<?php

namespace modules\task\models\query;

use modules\core\db\ActiveQuery;
use modules\task\models\TaskAssignee;

/**
 * This is the ActiveQuery class for [[\modules\task\models\TaskAssignee]].
 *
 * @see \modules\task\models\TaskAssignee
 */
class TaskAssigneeQuery extends ActiveQuery
{

    /**
     * @inheritdoc
     *
     * @return TaskAssignee[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return TaskAssignee|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
