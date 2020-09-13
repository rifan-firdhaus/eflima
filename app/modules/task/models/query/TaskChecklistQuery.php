<?php

namespace modules\task\models\query;

use modules\core\db\ActiveQuery;
use modules\task\models\TaskChecklist;

/**
 * This is the ActiveQuery class for [[\modules\task\models\TaskChecklist]].
 *
 * @see \modules\task\models\TaskChecklist
 */
class TaskChecklistQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return TaskChecklist[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return TaskChecklist|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param bool $isChecked
     *
     * @return $this
     */
    public function checked($isChecked = true)
    {
        return $this->andWhere(["{$this->getAlias()}.is_checked" => $isChecked]);
    }

    /**
     * @return $this
     */
    public function unchecked()
    {
        return $this->checked(false);
    }
}
