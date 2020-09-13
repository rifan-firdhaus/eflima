<?php

namespace modules\task\models\query;

use modules\core\db\ActiveQuery;
use modules\task\models\TaskInteraction;

/**
 * This is the ActiveQuery class for [[TaskInteraction]].
 *
 * @see TaskInteraction
 */
class TaskInteractionQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return TaskInteraction[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TaskInteraction|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
