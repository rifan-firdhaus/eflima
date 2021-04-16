<?php

namespace modules\task\models\query;

use modules\account\models\Staff;
use modules\core\db\ActiveQuery;
use modules\task\models\Task;
use modules\task\models\TaskAssignee;
use modules\task\models\TaskTimer;
use yii\base\InvalidConfigException;
use yii\db\Expression;

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

    /**
     * @param Staff $staff
     *
     * @return $this
     *
     * @throws InvalidConfigException
     */
    public function visibleToStaff($staff)
    {
        $assigneeQuery = TaskAssignee::find()
            ->joinWith('assignee')
            ->select('assignee_id')
            ->andWhere(new Expression("task_assignee.task_id=task.id"))
            ->createCommand()
            ->rawSql;

        $this->andWhere([
            'OR',
            ['task.visibility' => Task::VISIBILITY_PUBLIC],
            [
                'task.visibility' => Task::VISIBILITY_PRIVATE,
                'task.creator_id' => $staff->account->id,
            ],
            [
                'AND',
                ['task.visibility' => [Task::VISIBILITY_INVOLVED]],
                [
                    'OR',
                    ['task.creator_id' => $staff->account->id],
                    new Expression("'{$staff->id}' IN ($assigneeQuery)"),
                ],
            ],
        ]);

        return $this;
    }
}
