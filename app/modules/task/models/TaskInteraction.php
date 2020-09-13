<?php

namespace modules\task\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\components\notification\DatabaseNotificationChannel;
use modules\account\components\notification\Notification;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\task\models\query\TaskInteractionQuery;
use Yii;
use yii\db\Exception as DbException;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Staff            $staff
 * @property Task             $task
 * @property TaskStatus       $status
 * @property TaskAttachment[] $attachments
 * @property bool             $isMe
 *
 * @property int              $id               [int(10) unsigned]
 * @property int              $task_id          [int(11) unsigned]
 * @property int              $staff_id         [int(11) unsigned]
 * @property string           $type             [char(1)]
 * @property int              $status_id        [int(11) unsigned]
 * @property string           $comment
 * @property string           $progress         [decimal(5,4)]
 * @property int              $at               [int(11) unsigned]
 */
class TaskInteraction extends ActiveRecord
{
    public $uploaded_attachments;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_interaction}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['comment'], 'required', 'on' => ['admin/add']],
            [
                ['progress'],
                'double',
                'min' => 0,
                'max' => 100,
                'skipOnEmpty' => true,
                'on' => ['admin/add'],
            ],
            [
                'progress',
                'filter',
                'filter' => function ($value) {
                    if ($value === null) {
                        return null;
                    }

                    return $value / 100;
                },
                'on' => ['admin/add'],
            ],
            [
                'status_id',
                'exist',
                'targetClass' => TaskStatus::class,
                'targetAttribute' => ['status_id' => 'id'],
            ],
            [
                'uploaded_attachments',
                'each',
                'rule' => [
                    'file',
                ],
            ],
            [['comment'], 'string'],
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
            'staff' => Yii::t('app', 'Staff'),
            'type' => Yii::t('app', 'Type'),
            'status_id' => Yii::t('app', 'Status'),
            'progress' => Yii::t('app', 'Progress'),
            'at' => Yii::t('app', 'At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        //        $behaviors['timestamp'] = [
        //            'class' => TimestampBehavior::class,
        //            'updatedAtAttribute' => false,
        //            'createdAtAttribute' => 'at',
        //        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios['admin/task/add'] = [];
        $scenarios['admin/add'] = [];
        $scenarios['admin/update'] = [];

        return ArrayHelper::merge($scenarios, parent::scenarios());
    }

    /**
     * @return ActiveQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'staff_id'])->alias('staff_of_interaction');
    }

    /**
     * @return ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id'])->alias('task_of_interaction');
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(TaskStatus::class, ['id' => 'status_id'])->alias('status_of_interaction');
    }

    /**
     * @return ActiveQuery|TaskAttachment
     */
    public function getAttachments()
    {
        return $this->hasMany(TaskInteractionAttachment::class, ['interaction_id' => 'id']);
    }

    /**
     * @inheritdoc
     *
     * @return TaskInteractionQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TaskInteractionQuery(get_called_class());

        return $query->alias("task_interaction");
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        if ($this->scenario === 'admin/add' && $this->isNewRecord && !empty($this->task_id)) {
            if (empty($this->status_id)) {
                $this->status_id = $this->task->status_id;
            }

            if (!isset($this->progress)) {
                $this->progress = $this->task->progress;
            }
        }

        return parent::loadDefaultValues($skipIfSet);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->scenario === 'admin/add') {
            if ($this->status_id == $this->task->status_id) {
                $this->status_id = null;
            }

            if ($this->progress == $this->task->progress || $this->task->progress_calculation != Task::PROGRESS_CALCULATION_OWN) {
                $this->progress = null;
            }
        }

        if (!isset($this->at)) {
            $this->at = time();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->task->_fromInteraction = true;

        $isStatusUpdated = array_key_exists('status_id', $changedAttributes) && isset($this->status_id);
        $isProgressUpdated = array_key_exists('progress', $changedAttributes) && isset($this->progress) && $this->task->progress_calculation == Task::PROGRESS_CALCULATION_OWN;

        if (
            $isStatusUpdated &&
            $this->scenario == 'admin/add'
        ) {
            if (!$this->task->changeStatus($this->status_id)) {
                throw new DbException('Failed to change task status');
            }
        }

        if (
            $isProgressUpdated &&
            $this->scenario === 'admin/add'
        ) {
            if (!$this->task->updateProgress($this->progress)) {
                throw new DbException('Failed to save task progress');
            }
        }

        if ($this->scenario === 'admin/add') {
            $notifyTo = $this->task->getAssignees()->select('account_id')->createCommand()->queryColumn();

            $notification = new Notification([
                'to' => $notifyTo,
                'titleParams' => [
                    'task' => $this->task->title,
                ],
                'channels' => [
                    DatabaseNotificationChannel::class => [
                        'url' => ['/task/admin/task/view', 'id' => $this->task_id],
                        'is_internal_url' => true,
                    ],
                ],
            ]);

            if ($isProgressUpdated && $isStatusUpdated) {
                $notification->title = 'Task {task} status & progress updated';
                $notification->body = 'Status updated to {status}, progress updated to {progress}%';
                $notification->bodyParams = [
                    'status' => $this->status->label,
                    'progress' => $this->progress * 100,
                ];
            } elseif ($isStatusUpdated) {
                $notification->title = 'Task {task} status updated to {status}';
                $notification->body = StringHelper::truncate(strip_tags($this->comment), 50);
                $notification->titleParams['status'] = $this->status->label;
            } elseif ($isProgressUpdated) {
                $notification->title = 'Task {task} progress updated to {progress}%';
                $notification->body = StringHelper::truncate(strip_tags($this->comment), 50);
                $notification->titleParams['progress'] = $this->progress * 100;
            } else {
                $notification->title = 'Post comment on task {task}';
                $notification->body = StringHelper::truncate(strip_tags($this->comment), 50);
            }

            $notification->send();
        }

        // Save attachments
        if ($this->uploaded_attachments) {
            if (!$this->saveAttachments()) {
                throw new DbException('Failed to save Attachment');
            }
        }
    }


    /**
     * @return bool
     */
    protected function saveAttachments()
    {
        foreach ($this->uploaded_attachments AS $attachment) {
            $model = new TaskInteractionAttachment([
                'uploaded_file' => $attachment,
                'interaction_id' => $this->id,
            ]);

            if (!$model->save()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param null|int|string $staffId
     *
     * @return bool
     */
    public function getIsMe($staffId = null)
    {
        if (!$staffId) {
            /** @var StaffAccount $account */
            $account = Yii::$app->user->identity;
            $staffId = $account->profile->id;
        }

        return $staffId == $this->staff_id;
    }
}
