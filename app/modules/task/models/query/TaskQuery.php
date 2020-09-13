<?php

namespace modules\task\models\query;

use modules\core\db\ActiveQuery;
use modules\task\models\Task;
use modules\task\models\TaskTimer;
use function time;

/**
 * This is the ActiveQuery class for [[\modules\task\models\Task]].
 *
 * @see \modules\task\models\Task
 */
class TaskQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return Task[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Task|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @return $this
     */
    public function overdue()
    {
        return $this->andWhere(['<=', "{$this->getAlias()}.deadline_date", time()]);
    }

    /**
     * @param $starterId
     *
     * @return $this
     */
    public function runningTimer($starterId = null)
    {
        return $this->join('LEFT JOIN', ['task_timer' => TaskTimer::tableName()], [
            "AND",
            "[[task_timer.task_id]] = {$this->getAlias()}.[[id]]",
            ['task_timer.stopped_at' => null],
            [
                'OR',
                [
                    "{$this->getAlias()}.timer_type" => Task::TIMER_TYPE_INDIVIDUAL,
                    'task_timer.starter_id' => $starterId,
                ],
                [
                    "{$this->getAlias()}.timer_type" => Task::TIMER_TYPE_GLOBAL,
                ],

            ],
        ])->andWhere(['IS NOT', "task_timer.started_at", null]);
    }
}
