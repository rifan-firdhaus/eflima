<?php

namespace modules\task\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\account\components\notification\DatabaseNotificationChannel;
use modules\account\components\notification\Notification;
use modules\account\models\Staff;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\task\models\query\TaskAssigneeQuery;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\ModelEvent;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use function array_merge;
use function in_array;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Staff $assignee
 * @property Staff $assignor
 * @property Task  $task
 * @property array $historyParams
 *
 * @property int   $id          [int(10) unsigned]
 * @property int   $task_id     [int(11) unsigned]
 * @property int   $assignee_id [int(11) unsigned]
 * @property int   $assigned_at [int(11) unsigned]
 * @property int   $assignor_id [int(11) unsigned]
 */
class TaskAssignee extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_assignee}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['task_id', 'assignee_id'],
                'required',
                'on' => ['admin/add', 'admin/update', 'admin/task/add'],
            ],
            [
                ['assignee_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Staff::class,
                'targetAttribute' => ['assignee_id' => 'id'],
            ],
            [
                ['task_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Task::class,
                'targetAttribute' => ['task_id' => 'id'],
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
            'task_id' => Yii::t('app', 'Task ID'),
            'assignee_id' => Yii::t('app', 'Assignee ID'),
            'assigned_at' => Yii::t('app', 'Assigned At'),
            'assignor_id' => Yii::t('app', 'Assignor ID'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
            'createdAtAttribute' => 'assigned_at',
            'updatedAtAttribute' => false,
        ];

        return $behaviors;
    }

    /**
     * @return ActiveQuery
     */
    public function getAssignee()
    {
        return $this->hasOne(Staff::class, ['id' => 'assignee_id'])->alias('profile_of_assignee');
    }

    /**
     * @return ActiveQuery
     */
    public function getAssignor()
    {
        return $this->hasOne(Staff::class, ['id' => 'assignor_id'])->alias('assignor_of_assignee');
    }

    /**
     * @return ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id'])->alias('task_of_assignee');
    }

    /**
     * @inheritdoc
     *
     * @return TaskAssigneeQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TaskAssigneeQuery(get_called_class());

        return $query->alias("task_assignee");
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['admin/add'] = $scenarios['default'];
        $scenarios['admin/update'] = $scenarios['admin/add'];
        $scenarios['admin/task/add'] = $scenarios['admin/add'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && in_array($this->scenario, ['admin/task/add', 'admin/add'])) {
            $this->recordAssignedHistory();
        }

        if ($insert && $this->scenario === 'admin/add') {
            self::sendAssignNotification([$this], $this->task, $this->assignor);
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $this->recordRemoveAssignementHistory();
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function recordAssignedHistory()
    {
        return Account::history()->save('task_assignee.add', [
            'params' => $this->getHistoryParams(),
            'description' => 'Assigning {assignee_name} to task "{task_title}"',
            'tag' => 'assign',
            'model' => Task::class,
            'model_id' => $this->task_id,
        ]);
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function recordRemoveAssignementHistory()
    {
        return Account::history()->save('task_assignee.delete', [
            'params' => $this->getHistoryParams(),
            'description' => 'Removing assignment of {assignee_name} from task "{task_title}"',
            'tag' => 'release_assignment',
            'model' => Task::class,
            'model_id' => $this->task_id,
        ]);
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['assignee_id', 'assignor_id', 'task_id']);

        return array_merge($params, [
            'assignee_name' => $this->assignee->name,
            'assignor_name' => $this->assignor->name,
            'task_title' => $this->task->title,
        ]);
    }

    /**
     * @param TaskAssignee[] $assignees
     * @param Task           $task
     * @param Staff          $assignor
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public static function sendAssignNotification($assignees, $task, $assignor)
    {
        if (!$assignor instanceof Staff) {
            $assignor = Staff::find()->andWhere(['id' => $assignor])->one();

            if (!$assignor) {
                throw new Exception('Invalid assignor');
            }
        }

        $assignees = array_filter($assignees, function ($assignee) {
            return $assignee->assignee_id != $assignee->assignor_id;
        });

        if (empty($assignees)) {
            return;
        }

        $accountIds = ArrayHelper::getColumn($assignees, 'assignee.account_id');

        $notification = new Notification([
            'to' => $accountIds,
            'title' => '{assignor} assign a task to you',
            'titleParams' => [
                'assignor' => $assignor->name,
            ],
            'body' => $task->title,
            'channels' => [
                DatabaseNotificationChannel::class => [
                    'url' => ['/task/admin/task/view', 'id' => $task->id],
                    'is_internal_url' => true,
                ],
            ],
        ]);

        $notification->send();
    }


    /**
     * @param ModelEvent $event
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public static function deleteAllAssigneeRelatedToDeletedStaff($event)
    {
        /** @var Staff $model */
        $staff = $event->sender;

        $taskAssignees = TaskAssignee::find()
            ->andWhere([
                'OR',
                ['assignee_id' => $staff->id],
                ['assignor_id' => $staff->id],
            ])
            ->all();

        foreach ($taskAssignees AS $assignee) {
            if (!$assignee->delete()) {
                throw new Exception('Failed to delete related task assignee');
            }
        }
    }
}
