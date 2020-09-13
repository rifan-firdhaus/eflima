<?php namespace modules\project\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use Exception;
use modules\account\Account;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\validators\DateValidator;
use modules\crm\models\Customer;
use modules\project\models\queries\ProjectMilestoneQuery;
use modules\project\models\queries\ProjectQuery;
use modules\task\models\query\TaskQuery;
use modules\task\models\Task;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\Exception as DbException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Project            $project
 * @property Task[]|array       $tasks
 * @property ActiveDataProvider $taskDataProvider
 *
 * @property null|string        $colorLabel
 *
 * @property int                $id            [int(10) unsigned]
 * @property int                $project_id    [int(11) unsigned]
 * @property string             $name
 * @property string             $color         [varchar(7)]
 * @property string             $description
 * @property int                $started_date  [int(11) unsigned]
 * @property int                $deadline_date [int(11) unsigned]
 * @property int                $order         [int(11) unsigned]
 * @property int                $created_at    [int(11) unsigned]
 * @property int                $updated_at    [int(11) unsigned]
 */
class ProjectMilestone extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%project_milestone}}';
    }

    /**
     * @inheritdoc
     *
     * @return ProjectMilestoneQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new ProjectMilestoneQuery(get_called_class());

        return $query->alias("project_milestone");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['name'],
                'required',
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                'project_id',
                'exist',
                'targetRelation' => 'project',
            ],
            [
                'order',
                'integer',
            ],
            [
                ['description', 'deadline_date'],
                'string',
            ],
            [
                'started_date',
                'daterange',
                'type' => DateValidator::TYPE_DATETIME,
                'dateTo' => 'deadline_date',
                'startValidation' => [
                    'tooBig' => Yii::t('app', 'Started date must be less than deadline'),
                ],
                'endValidation' => [
                    'tooSmall' => Yii::t('app', 'Deadline must be greater than started date'),
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'project_id' => Yii::t('app', 'Project ID'),
            'name' => Yii::t('app', 'Name'),
            'color' => Yii::t('app', 'Color'),
            'description' => Yii::t('app', 'Description'),
            'started_date' => Yii::t('app', 'Started Date'),
            'deadline_date' => Yii::t('app', 'Deadline Date'),
            'order' => Yii::t('app', 'Order'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Set History
        if (
            in_array($this->scenario, ['admin/add', 'admin/update']) &&
            !empty($changedAttributes)
        ) {
            $this->recordSavedHistory($insert);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class
        ];

        return $behaviors;
    }

    /**
     * @param bool $color
     *
     * @return array|string|null
     */
    public static function colors($color = false)
    {
        $colors = [
            'yellow' => Yii::t('app', 'Yellow'),
            'green' => Yii::t('app', 'Green'),
            'red' => Yii::t('app', 'Red'),
            'blue' => Yii::t('app', 'Blue'),
            'purple' => Yii::t('app', 'Purple'),
            'orange' => Yii::t('app', 'Orange'),
            'cyan' => Yii::t('app', 'Cyan'),
            'teal' => Yii::t('app', 'Teal'),
            'indigo' => Yii::t('app', 'Indigo'),
            'pink' => Yii::t('app', 'Pink'),
        ];

        if ($color !== false) {
            return isset($colors[$color]) ? $colors[$color] : null;
        }

        return $colors;
    }

    /**
     * @return string|null
     */
    public function getColorLabel()
    {
        return self::colors($this->color);
    }

    /**
     * @return ActiveQuery|TaskQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['milestone_id' => 'id'])->alias('tasks_of_milestone');
    }

    /**
     * @return ActiveDataProvider
     */
    public function getTaskDataProvider()
    {
        return new ActiveDataProvider([
            'query' => $this->getTasks(),
            'sort' => ['defaultOrder' => ['milestone_order' => SORT_ASC]],
        ]);
    }

    /**
     * @return ActiveQuery|ProjectQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'project_id'])->alias('project_of_milestone');
    }

    /**
     * @param string|int $project_id
     * @param array      $sort
     *
     * @return bool
     *
     * @throws Throwable
     * @throws InvalidConfigException
     * @throws DbException
     */
    public static function sort($project_id, $sort = [])
    {
        $models = self::find()->andWhere(['project_id' => $project_id, 'id' => $sort])->indexBy('id')->all();

        $transaction = self::getDb()->beginTransaction();

        try {
            foreach ($sort AS $order => $milestoneId) {
                if (!isset($models[$milestoneId])) {
                    continue;
                }

                $model = $models[$milestoneId];

                $model->order = $order;

                if (!$model->save(false)) {
                    $transaction->rollBack();

                    return false;
                }
            }
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        $transaction->commit();

        return true;
    }

    /**
     * @param       $taskId
     * @param       $milestoneId
     * @param array $sort
     *
     * @return bool
     *
     * @throws DbException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function moveTask($taskId, $milestoneId, $sort = [])
    {
        $task = Task::find()->andWhere(['id' => $taskId])->one();

        if (!$task) {
            return false;
        }

        $milestone = ProjectMilestone::find()->andWhere(['id' => $milestoneId])->one();

        if (!$milestone) {
            return false;
        }

        $task->milestone_id = $milestone->id;

        if (!$task->save(false)) {

            return false;
        }

        $this->recordTaskMovedHistory($task, $milestone);

        if (!$milestone->sortTask($sort)) {
            return false;
        }

        return true;
    }

    /**
     * @param $sort
     *
     * @return bool
     *
     * @throws DbException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function sortTask($sort)
    {
        $models = Task::find()->andWhere(['milestone_id' => $this->id, 'id' => $sort])->indexBy('id')->all();

        $transaction = Task::getDb()->beginTransaction();

        try {
            foreach ($sort AS $order => $taskId) {
                if (!isset($models[$taskId])) {
                    continue;
                }

                $model = $models[$taskId];

                $model->milestone_order = $order;

                if (!$model->save(false)) {
                    $transaction->rollBack();

                    return false;
                }
            }
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        $transaction->commit();

        return true;
    }

    /**
     * @param string $color
     *
     * @return bool
     */
    public function changeColor($color)
    {
        $this->color = $color;

        return $this->save(false);
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'project_id', 'name']);

        $params['project_name'] = $this->project->name;

        return $params;
    }

    /**
     * @param Task             $task
     * @param ProjectMilestone $toMilestone
     *
     * @return bool
     *
     * @throws DbException
     * @throws Throwable
     */
    public function recordTaskMovedHistory($task, $toMilestone)
    {
        $history = [
            'params' => $this->getHistoryParams(),
            'description' => 'Moving task "{task_title}" from milestone "{name}" to "{to_name}"',
            'model' => Project::class,
            'model_id' => $this->project_id
        ];

        $history['params']['to_id'] = $toMilestone->id;
        $history['params']['to_name'] = $toMilestone->name;
        $history['params']['task_id'] = $task->id;
        $history['params']['task_title'] = $task->title;

        $history['tag'] = 'update';

        return Account::history()->save('project_milestone.move_task', $history);
    }

    /**
     * @param bool $insert
     *
     * @return bool
     * @throws DbException
     * @throws Throwable
     */
    public function recordSavedHistory($insert = false)
    {
        $history = [
            'params' => $this->getHistoryParams(),
            'model' => Project::class,
            'model_id' => $this->project_id
        ];

        if ($this->scenario === 'admin/add' && $insert) {
            $history['description'] = 'Adding milestone "{name}" to project "{name}"';
        } else {
            $history['description'] = 'Updating {project_name}\'s milestone "{name}"';
        }

        $historyEvent = $this->scenario === 'admin/add' ? 'project_milestone.add' : 'project_milestone.update';
        $history['tag'] = $this->scenario === 'admin/add' ? 'add' : 'update';

        return Account::history()->save($historyEvent, $history);
    }
}
