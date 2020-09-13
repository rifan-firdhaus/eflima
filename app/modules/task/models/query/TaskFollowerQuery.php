<?php

namespace modules\task\models\query;

use modules\core\db\ActiveQuery;
use modules\task\models\TaskFollower;

/**
 * This is the ActiveQuery class for [[\modules\task\models\TaskFollower]].
 *
 * @see \modules\task\models\TaskFollower
 */
class TaskFollowerQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return TaskFollower[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return TaskFollower|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
