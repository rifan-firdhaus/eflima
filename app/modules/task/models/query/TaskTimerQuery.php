<?php

namespace modules\task\models\query;

use modules\core\db\ActiveQuery;
use modules\task\models\TaskTimer;

/**
 * This is the ActiveQuery class for [[\modules\task\models\TaskTimer]].
 *
 * @see \modules\task\models\TaskTimer
 */
class TaskTimerQuery extends ActiveQuery
{

    /**
     * @inheritdoc
     * @return TaskTimer[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TaskTimer|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @return TaskTimerQuery
     */
    public function started()
    {
        return $this->andWhere(["{$this->getAlias()}.stopped_at" => null]);
    }

    /**
     * @return TaskTimerQuery
     */
    public function stopped()
    {
        return $this->andWhere(["IS NOT", "{$this->getAlias()}.stopped_at", null]);
    }

    /**
     * @return int
     */
    public function totalDuration()
    {
        return (int) $this->sum("[[{$this->getAlias()}.stopped_at]] - [[{$this->getAlias()}.started_at]]");
    }

    /**
     * @param int $timeStart
     * @param int $timeEnd
     *
     * @return int
     */
    public function totalDurationInTimeRange($timeStart, $timeEnd)
    {
        $this->inTimeRange($timeStart, $timeEnd);

        $this->addParams([
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
        ]);

        return (int) $this->sum("IF( [[{$this->getAlias()}.stopped_at]] >= :time_end, :time_end, [[{$this->getAlias()}.stopped_at]] ) - IF( [[{$this->getAlias()}.started_at]] <= :time_start, :time_start , [[{$this->getAlias()}.started_at]] )");
    }

    /**
     * @param int $timeStart
     * @param int $timeEnd
     *
     * @return $this
     */
    public function inTimeRange($timeStart, $timeEnd)
    {
        return $this->andWhere([
            'OR',
            [
                'AND',
                ['>=', "{$this->getAlias()}.started_at", $timeStart],
                ['<=', "{$this->getAlias()}.started_at", $timeEnd],
            ],
            [
                'AND',
                ['>=', "{$this->getAlias()}.stopped_at", $timeStart],
                ['<=', "{$this->getAlias()}.stopped_at", $timeEnd],
            ],
            [
                'AND',
                ['<=', "{$this->getAlias()}.started_at", $timeStart],
                ['>=', "{$this->getAlias()}.stopped_at", $timeEnd],
            ],
        ]);
    }
}
