<?php namespace modules\task\models\forms\task;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\Staff;
use modules\core\components\SearchableModelEvent;
use modules\core\helpers\Common;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\core\validators\DateValidator;
use modules\task\models\query\TaskQuery;
use modules\task\models\Task;
use modules\task\models\TaskAssignee;
use modules\task\models\TaskInteraction;
use modules\task\models\TaskPriority;
use modules\task\models\TaskStatus;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use function array_merge;
use function array_sum;
use function is_array;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Task[]             $query
 * @property ActiveDataProvider $dataProvider
 */
class TaskSearch extends Task implements SearchableModel
{
    use SearchableModelTrait;

    /** @var Staff */
    public $currentStaff;

    public $q;
    public $started_date_from;
    public $started_date_to;
    public $deadline_date_from;
    public $deadline_date_to;
    public $created_at_from;
    public $created_at_to;
    public $assigned_to_me;
    public $overdue;

    public function init()
    {


        $this->setAssociateSort([
            'task' => [
                'model' => Task::instance(),
                'alias' => 'task',
                'except' => [
                    'creator_id',
                    'updater_id'
                ]
            ]
        ]);

        parent::init();
    }

    public function rules()
    {
        return [
            [['q', 'started_date_to', 'deadline_date_to', 'created_at_to'], 'string'],
            [['overdue', 'assigned_to_me'], 'boolean'],
            [
                'assigned_to_me',
                'boolean',
                'on' => 'admin/search',
            ],
            [
                'started_date_from',
                'daterange',
                'fullDay' => true,
                'type' => DateValidator::TYPE_DATE,
                'dateTo' => 'started_date_to',
            ],
            [
                'deadline_date_from',
                'daterange',
                'fullDay' => true,
                'type' => DateValidator::TYPE_DATE,
                'dateTo' => 'deadline_date_to',
            ],
            [
                'created_at_from',
                'daterange',
                'fullDay' => true,
                'type' => DateValidator::TYPE_DATE,
                'dateTo' => 'created_at_to',
            ],
            [
                ['priority_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => TaskPriority::class,
                'targetAttribute' => ['priority_id' => 'id'],
            ],
            [
                ['status_id'],
                'each',
                'when' => function ($model) {
                    return is_array($model->status_id);
                },
                'rule' => [
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => TaskStatus::class,
                    'targetAttribute' => ['status_id' => 'id'],
                ],
            ],
            [
                ['assignee_ids'],
                'each',
                'when' => function ($model) {
                    return is_array($model->assignee_ids);
                },
                'rule' => [
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => Staff::class,
                    'targetAttribute' => ['assignee_ids' => 'id'],
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        unset($behaviors['attributeTypecast']);

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'q' => Yii::t('app', 'Keyword'),
            'assigned_to_me' => Yii::t('app', 'Show only task assigned to me'),
            'overdue' => Yii::t('app', 'Show only overdue task'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['admin/search'] = $scenarios['default'];

        return $scenarios;
    }

    /**
     * @return array
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function getStatusSummary()
    {
        $query = clone $this->getQuery();

        $statuses = $query->groupBy('task.status_id')->joinWith('status')->select(['status_of_task.*', 'count' => "COUNT([[task.id]])"])->createCommand()->queryAll();
        $total = array_sum(ArrayHelper::getColumn($statuses, 'count'));
        $remainingStatuses = TaskStatus::find()->andWhere(['NOT IN', 'id', ArrayHelper::getColumn($statuses, 'id')])->asArray()->all();
        $statuses = ArrayHelper::merge($statuses, $remainingStatuses);

        foreach ($statuses AS &$status) {
            if (!isset($status['count'])) {
                $status['count'] = 0;
            }

            $status['ratio'] = $status['count'] > 0 ? $status['count'] / $total : 0;
        }

        ArrayHelper::multisort($statuses, 'order');

        return $statuses;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function getProgressStatistic()
    {
        $query = clone $this->getQuery();

        $timeStart = strtotime(date('Y-m-01 00:00:00'));
        $timeEnd = strtotime(date('Y-m-t 23:59:59'));
        $interactionQuery = TaskInteraction::find()
            ->andWhere(['task_interaction.task_id' => $query->select('task.id')])
            ->andWhere(["IS NOT", 'task_interaction.progress', null]);

        $interactionMaxQuery = clone $interactionQuery;
        $interactionMaxQuery->select('MAX([[task_interaction.id]])')->groupBy(['task_interaction.task_id', 'DATE(FROM_UNIXTIME(task_interaction.at))']);

        $interactions = $interactionQuery
            ->addSelect(["task_interaction.*", 'FROM_UNIXTIME(task_interaction.at)'])
            ->andWhere(['task_interaction.id' => $interactionMaxQuery])
            ->andWhere([
                'AND',
                ['>=', 'task_interaction.at', $timeStart],
                ['<=', 'task_interaction.at', $timeEnd],
            ])
            ->orderBy(['task_interaction.at' => SORT_ASC])
            ->createCommand()
            ->queryAll();

        $group = [];
        $taskIds = [];

        foreach ($interactions AS $interaction) {
            $date = strtotime(date('Y-m-d 00:00:00', $interaction['at']));

            $group[$date][$interaction['task_id']] = $interaction['progress'];
            $taskIds[] = $interaction['task_id'];
        }

        $taskIds = array_unique($taskIds);

        $current = $timeStart;
        $lastGroup = [];
        $result = [];

        while ($current < $timeEnd) {
            foreach ($taskIds AS $taskId) {
                if (!isset($result[$current])) {
                    $result[$current] = [
                        'value' => 0,
                        'count' => 0,
                    ];
                }

                if (isset($group[$current][$taskId])) {
                    $lastGroup[$taskId] = $group[$current][$taskId];
                    $value = $group[$current][$taskId];
                } elseif (isset($lastGroup[$taskId])) {
                    $value = $lastGroup[$taskId];
                } else {
                    continue;
                }

                $result[$current]['value'] += $value;
                $result[$current]['count'] += 1;
            }

            $current += 86400; // add 1 day;
        }

        ksort($result);

        $formatted = [];

        foreach ($result AS $time => $item) {
            $average = $item['count'] > 0 ? $item['value'] / $item['count'] : 0;

            $formatted[] = [$time * 1000, round($average * 100, 2)];
        }

        return [
            'series' => $formatted,
        ];
    }

    /**
     * @inheritDoc
     *
     * @param TaskQuery|ActiveQuery
     *
     * @return TaskQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        // Filter by archive
        $query->andFilterWhere(['task.is_archieved' => $this->is_archieved]);

        // Filter by started date
        $query->andFilterWhere(['<=', 'task.started_date', $this->started_date_to])
            ->andFilterWhere(['>=', 'task.started_date', $this->started_date_from]);

        // Filter by deadline date
        $query->andFilterWhere(['<=', 'task.deadline_date', $this->deadline_date_to])
            ->andFilterWhere(['>=', 'task.deadline_date', $this->deadline_date_from]);

        // Filter by created at date
        $query->andFilterWhere(['<=', 'task.created_at', $this->created_at_to])
            ->andFilterWhere(['>=', 'task.created_at', $this->created_at_from]);

        // Filter by status
        $query->andFilterWhere(['task.status_id' => $this->status_id]);

        // Filter by priority
        $query->andFilterWhere(['task.priority_id' => $this->priority_id]);

        // Show only overdue task
        if (isset($this->overdue) && $this->overdue) {
            $query->overdue();
        }

        // Show only task assigned to the current staff
        if (isset($this->assigned_to_me) && $this->assigned_to_me) {
            $query->leftJoin(['current_task_assignee' => TaskAssignee::tableName()], [
                'AND',
                "[[current_task_assignee.task_id]] = [[task.id]]",
                ['current_task_assignee.assignee_id' => $this->currentStaff->id],
            ])->andWhere(['IS NOT', 'current_task_assignee.id', null]);
        }

        // Filter by assignee
        if (!Common::isEmpty($this->assignee_ids)) {
            $query->leftJoin(['task_assignee' => TaskAssignee::tableName()], [
                'AND',
                "[[task_assignee.task_id]] = [[task.id]]",
                ['task_assignee.assignee_id' => $this->assignee_ids],
            ])->andWhere(['IS NOT', 'task_assignee.id', null]);
        }

        // Filter by query string
        $query->andFilterWhere([
            'OR',
            ['LIKE', 'task.title', $this->q],
            ['LIKE', 'task.description', $this->q],
        ]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));
    }

    /**
     * @inheritdoc
     *
     * @return TaskQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Task::find();

        if ($this->currentStaff) {
            $this->_query->visibleToStaff($this->currentStaff);
        }

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        $relatedModelConditions = [];

        if (!empty($this->params['models'])) {
            foreach ($this->params['models'] AS $relation) {
                if ($relation instanceof Closure) {
                    $relatedModelCondition = call_user_func($relation, $this->_query);

                    if ($relatedModelCondition) {
                        $relatedModelConditions[] = $relatedModelCondition;
                    }
                } else {
                    $relatedModelCondition = ['task.model' => $relation['model']];

                    if (isset($relation['model_id'])) {
                        $relatedModelCondition['task.model_id'] = $relation['model_id'];
                    }

                    $relatedModelConditions[] = $relatedModelCondition;
                }
            }
        }

        if (!empty($this->params['model'])) {
            $relatedModelCondition = ['task.model' => $this->params['model']];

            if (!empty($this->params['model_id'])) {
                $relatedModelCondition['task.model_id'] = $this->params['model_id'];
            }

            $relatedModelConditions[] = $relatedModelCondition;
        }

        if (!empty($relatedModelConditions)) {
            array_unshift($relatedModelConditions, 'OR');

            $this->_query->andWhere($relatedModelConditions);
        }

        return $this->_query;
    }
}
