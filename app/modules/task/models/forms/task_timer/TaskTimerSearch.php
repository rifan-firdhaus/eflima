<?php namespace modules\task\models\forms\task_timer;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\helpers\Common;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\task\models\query\TaskTimerQuery;
use modules\task\models\Task;
use modules\task\models\TaskTimer;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\validators\DateValidator;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property TaskTimer          $query
 * @property ActiveDataProvider $dataProvider
 * @property int                $totalDurationToday
 * @property int                $totalDurationThisWeek
 * @property array              $statistic
 * @property int                $totalDuration
 */
class TaskTimerSearch extends TaskTimer implements SearchableModel
{
    use SearchableModelTrait;

    /** @var Task|null */
    public $currentTask;

    public $date_from;
    public $date_to;

    public function init()
    {
        parent::init();

        $this->dataProvider->sort->defaultOrder = ['started_at' => SORT_DESC];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'date_from',
                'daterange',
                'fullDay' => false,
                'type' => DateValidator::TYPE_DATETIME,
                'dateTo' => 'date_to',
            ],
            [['task_id', 'date_from', 'date_to', 'starter_id', 'stopper_id'], 'safe'],
        ];
    }

    /**
     * @return array
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function getStatistic()
    {
        $query = clone $this->getQuery();

        $span = 86400;
        $timeStart = strtotime(date('Y-m-01 00:00:00'));
        $timeEnd = strtotime(date('Y-m-t 23:59:59'));
        $models = $query->inTimeRange($timeStart, $timeEnd)->createCommand()->queryAll();
        $result = [];

        foreach ($models AS $model) {
            $start = $model['started_at'] < $timeStart ? $timeStart : $model['started_at'];
            $end = $model['stopped_at'] > $timeEnd ? $timeEnd : $model['stopped_at'];
            $current = strtotime(date('Y-m-d 00:00:00', $start));

            while ($current < $end) {
                $diff = $end - $current;
                $diff = $diff > $span ? $span : $diff;

                if (!isset($result[$current])) {
                    $result[$current] = 0;
                }

                $result[$current] += $diff;

                if ($current < $start) {
                    $result[$current] -= $start - $current;
                }

                $current += $diff;
            }
        }

        $current = $timeStart;

        while ($current < $timeEnd) {
            if (!isset($result[$current])) {
                $result[$current] = 0;
            }

            $current += $span;
        }

        ksort($result);

        $formatted = [];

        foreach ($result AS $time => $record) {
            $formatted[] = [$time * 1000, $record];
        }


        return $formatted;
    }

    /**
     * @return int
     * @throws InvalidConfigException
     */
    public function getTotalDuration()
    {
        $query = clone $this->getQuery();

        return $query->totalDuration();
    }

    /**
     * @return int
     * @throws InvalidConfigException
     */
    public function getTotalDurationToday()
    {
        $query = clone $this->getQuery();

        $timeStart = strtotime(date('Y-m-d 00:00:00'));
        $timeEnd = strtotime(date('Y-m-d 23:59:59'));

        return $query->totalDurationInTimeRange($timeStart, $timeEnd);
    }

    /**
     * @return int
     * @throws InvalidConfigException
     */
    public function getTotalDurationThisWeek()
    {
        $query = clone $this->getQuery();

        $day = date('w');
        $timeStart = strtotime(date('Y-m-d 00:00:00', strtotime('-' . $day . ' days')));
        $timeEnd = strtotime(date('Y-m-d 23:59:59', strtotime('+' . (6 - $day) . ' days')));

        return $query->totalDurationInTimeRange($timeStart, $timeEnd);
    }

    /**
     * @inheritDoc
     *
     * @param null|ActiveQuery|TaskTimerQuery $query
     *
     * @return ActiveQuery|TaskTimerQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['task_timer.task_id' => $this->task_id])
            ->andFilterWhere(['task_timer.starter_id' => $this->starter_id])
            ->andFilterWhere(['task_timer.stopper_id' => $this->stopper_id]);

        if (!Common::isEmpty($this->date_to) && !Common::isEmpty($this->date_from)) {
            $query->inTimeRange($this->date_from, $this->date_to);
        }

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }

    /**
     * @return ActiveQuery|TaskTimerQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = TaskTimer::find();

        if (isset($this->params['task_id'])) {
            $this->_query->andWhere(['task_timer.task_id' => $this->params['task_id']]);
        }

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }
}
