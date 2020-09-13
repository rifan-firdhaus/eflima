<?php

namespace modules\task\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\account\components\notification\DatabaseNotificationChannel;
use modules\account\components\notification\Notification;
use modules\account\models\Staff;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\validators\DateValidator;
use modules\task\models\query\TaskTimerQuery;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\Exception as DbException;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 *
 * @property Staff $approver
 * @property Task  $task
 * @property Staff $stopper
 * @property Staff $starter
 * @property array $historyParams
 *
 * @property int   $id          [int(10) unsigned]
 * @property int   $task_id     [int(11) unsigned]
 * @property int   $starter_id  [int(11) unsigned]
 * @property int   $stopper_id  [int(10) unsigned]
 * @property int   $started_at  [int(11) unsigned]
 * @property int   $stopped_at  [int(11) unsigned]
 * @property bool  $is_approved [tinyint(1) unsigned]
 * @property int   $approver_id [int(11) unsigned]
 * @property int   $updated_at  [int(11) unsigned]
 *
 */
class TaskTimer extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_timer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id'], 'required'],
            [
                ['starter_id', 'stopper_id', 'stopped_at', 'started_at'],
                'required',
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                'starter_id',
                'required',
                'on' => 'admin/start',
            ],
            [
                'started_at',
                'daterange',
                'type' => DateValidator::TYPE_DATETIME,
                'dateTo' => 'stopped_at',
                'startValidation' => [
                    'tooBig' => Yii::t('app', 'Start time must be less than stop time'),
                ],
                'endValidation' => [
                    'tooSmall' => Yii::t('app', 'Stop time must be greater than start time'),
                ],
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                ['starter_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Staff::class,
                'targetAttribute' => ['starter_id' => 'id'],
                'on' => ['admin/start', 'admin/add', 'admin/update'],
            ],
            [
                ['stopper_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Staff::class,
                'targetAttribute' => ['stopper_id' => 'id'],
                'on' => ['admin/add', 'admin/update'],
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
    public function scenarios()
    {
        $scenarios['admin/start'] = [];
        $scenarios['admin/stop'] = [];

        $scenarios['admin/add'] = [];
        $scenarios['admin/update'] = [];

        return ArrayHelper::merge($scenarios, parent::scenarios());
    }

    public function transactions()
    {
        return [
            'admin/start' => self::OP_ALL,
            'admin/stop' => self::OP_ALL,
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
            'starter_id' => Yii::t('app', 'Started By'),
            'started_at' => Yii::t('app', 'Started At'),
            'stopper_id' => Yii::t('app', 'Stopped By'),
            'stopped_at' => Yii::t('app', 'Stopped At'),
            'is_approved' => Yii::t('app', 'Is Approved'),
            'approver_id' => Yii::t('app', 'Approver ID'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getStarter()
    {
        return $this->hasOne(Staff::class, ['id' => 'starter_id'])->alias('starter_of_timer');
    }

    /**
     * @return ActiveQuery
     */
    public function getStopper()
    {
        return $this->hasOne(Staff::class, ['id' => 'stopper_id'])->alias('stopper_of_timer');
    }

    /**
     * @return ActiveQuery
     */
    public function getApprover()
    {
        return $this->hasOne(Staff::className(), ['id' => 'approver_id'])->alias('approver_of_timer');
    }

    /**
     * @return ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id'])->alias('task_of_timer');
    }

    /**
     * @inheritdoc
     *
     * @return TaskTimerQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TaskTimerQuery(get_called_class());

        return $query->alias("task_timer");
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->scenario == 'admin/start' && $insert) {
            $this->started_at = time();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && $this->scenario === 'admin/start') {
            $this->sendStartedNotifification();
            $this->recordStartedHistory();
        } elseif ($this->scenario === 'admin/stop') {
            $this->sendStoppeddNotifification();
            $this->recordStoppedHistory();
        } elseif (
            in_array($this->scenario, ['admin/add', 'admin/update']) &&
            !empty($changedAttributes)
        ) {
            $this->recordSavedHistory();
        }
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    protected function sendStartedNotifification()
    {
        $notifyTo = $this->task->getAssigneesRelationship()
            ->joinWith(['assignee'])
            ->select('profile_of_assignee.account_id')
            ->createCommand()
            ->queryColumn();
        $notification = new Notification([
            'to' => $notifyTo,
            'title' => "Starting timer of task \"{task}\"",
            'titleParams' => [
                'task' => $this->task->title,
            ],
            'channels' => [
                DatabaseNotificationChannel::class => [
                    'url' => ['/task/admin/task/view', 'id' => $this->task_id],
                    'is_internal_url' => true,
                    'category' => 'task.timer.start',
                ],
            ],
        ]);

        $notification->send();
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    protected function sendStoppeddNotifification()
    {
        $notifyTo = $this->task->getAssigneesRelationship()
            ->joinWith(['assignee'])
            ->select('profile_of_assignee.account_id')
            ->createCommand()
            ->queryColumn();
        $notification = new Notification([
            'to' => $notifyTo,
            'title' => "Stopping timer of task \"{task}\"",
            'titleParams' => [
                'task' => $this->task->title,
            ],
            'channels' => [
                DatabaseNotificationChannel::class => [
                    'url' => ['/task/admin/task/view', 'id' => $this->task_id],
                    'is_internal_url' => true,
                    'category' => 'task.timer.stop',
                ],
            ],
        ]);

        $notification->send();
    }

    /**
     * @param integer $stopperId
     *
     * @return bool
     */
    public function stop($stopperId)
    {
        $this->scenario = 'admin/stop';
        $this->stopper_id = $stopperId;
        $this->stopped_at = time();

        return $this->save(false);
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'task_id', 'approver_id', 'started_at', 'stopped_at', 'starter_id', 'stopper_id']);

        $params['task_title'] = $this->task->title;
        $params['starter_name'] = $this->starter->name;
        $params['stopper_name'] = $this->stopper_id ? $this->stopper->name : null;
        $params['approver_name'] = $this->approver_id ? $this->approver->name : null;

        return $params;
    }

    /**
     * @return bool
     *
     * @throws Exception
     * @throws Throwable
     */
    protected function recordStartedHistory()
    {
        return Account::history()->save('task_timer.start', [
            'params' => $this->getHistoryParams(),
            'description' => 'Start timer of task "{task_title}"',
            'tag' => 'update',
            'model' => Task::class,
            'model_id' => $this->task_id,
        ]);
    }

    /**
     * @return bool
     *
     * @throws Exception
     * @throws Throwable
     */
    protected function recordStoppedHistory()
    {
        return Account::history()->save('task_timer.stop', [
            'params' => $this->getHistoryParams(),
            'description' => 'Stop timer of task "{task_title}"',
            'tag' => 'update',
            'model' => Task::class,
            'model_id' => $this->task_id,
        ]);
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
            'model' => Task::class,
            'model_id' => $this->task_id,
        ];

        if ($this->scenario === 'admin/add' && $insert) {
            $history['description'] = 'Adding time manually to "{task_title}"';
        } else {
            $history['description'] = 'Updating time record of task "{task_title}"';
        }

        $historyEvent = $this->scenario === 'admin/add' ? 'task_timer.add' : 'task_timer.update';
        $history['tag'] = $this->scenario === 'admin/add' ? 'add' : 'update';


        return Account::history()->save($historyEvent, $history);
    }
}
