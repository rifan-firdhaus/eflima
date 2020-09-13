<?php namespace modules\task\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use Exception;
use modules\account\Account;
use modules\account\models\queries\StaffQuery;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\components\Setting;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\validators\DateValidator;
use modules\task\components\TaskRelation;
use modules\task\models\queries\TaskAttachmentQuery;
use modules\task\models\query\TaskAssigneeQuery;
use modules\task\models\query\TaskChecklistQuery;
use modules\task\models\query\TaskPriorityQuery;
use modules\task\models\query\TaskQuery;
use modules\task\models\query\TaskStatusQuery;
use modules\task\models\query\TaskTimerQuery;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as DbException;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Staff                  $creator
 * @property Task                   $parent
 * @property Task[]                 $children
 * @property TaskPriority           $priority
 * @property TaskFollower[]         $followersRelationship
 * @property TaskAssignee[]         $assigneesRelationship
 * @property TaskStatus             $status
 * @property null|string            $estimationModifierText
 * @property null|string            $visibilityText
 * @property Staff[]                $assignees
 * @property null|string            $progressCalculationText
 * @property array                  $historyParams
 * @property null|string            $timerType
 * @property bool                   $isOverdue
 * @property bool                   $isStarted
 * @property float|int              $estimationSecond
 * @property null|string            $progressCalculationDisplay
 * @property TaskAttachment[]|array $attachments
 * @property TaskInteraction|bool   $currentInteraction
 * @property null|mixed             $relatedModel
 * @property null|TaskRelation      $relatedObject
 * @property TaskTimer[]|array      $timers
 * @property string|float|int       $totalRecordedTime
 *
 * @property int                    $id                                     [int(10) unsigned]
 * @property int                    $parent_id                              [int(11) unsigned]
 * @property string                 $model
 * @property string                 $model_id
 * @property int                    $status_id                              [int(11) unsigned]
 * @property int                    $priority_id                            [int(11) unsigned]
 * @property string                 $title
 * @property string                 $description
 * @property int                    $started_date                           [int(11) unsigned]
 * @property int                    $deadline_date                          [int(11) unsigned]
 * @property string                 $progress                               [decimal(5,4)]
 * @property string                 $estimation                             [decimal(7,2)]
 * @property string                 $estimation_modifier                    [char(1)]
 * @property bool                   $is_timer_enabled                       [tinyint(1) unsigned]
 * @property string                 $timer_type                             [char(1)]
 * @property bool                   $is_timer_active                        [tinyint(1) unsigned]
 * @property bool                   $is_individual_timer                    [tinyint(1) unsigned]
 * @property bool                   $is_billable                            [tinyint(1) unsigned]
 * @property bool                   $is_archieved                           [tinyint(1) unsigned]
 * @property string                 $price                                  [decimal(25,8)]
 * @property string                 $price_modifier                         [char(1)]
 * @property string                 $progress_calculation                   [char(1)]
 * @property string                 $visibility                             [char(1)]
 * @property bool                   $is_comment_allowed                     [tinyint(1) unsigned]
 * @property bool                   $is_customer_allowed_to_comment         [tinyint(1) unsigned]
 * @property bool                   $is_notified_when_comment               [tinyint(1) unsigned]
 * @property bool                   $is_notified_only_when_customer_comment [tinyint(1) unsigned]
 * @property bool                   $is_notified_when_progress_updated      [tinyint(1) unsigned]
 * @property bool                   $is_checklist_exists                    [tinyint(1) unsigned]
 * @property int                    $creator_id                             [int(11) unsigned]
 * @property int                    $created_at                             [int(11) unsigned]
 * @property int                    $updated_at                             [int(11) unsigned]
 * @property int                    $milestone_id                           [int(11) unsigned]
 * @property int                    $milestone_order                        [int(11) unsigned]
 */
class Task extends ActiveRecord
{
    const ESTIMATION_MODIFIER_HOUR = 'H';
    const ESTIMATION_MODIFIER_DAY = 'D';
    const ESTIMATION_MODIFIER_MONTH = 'M';

    const VISIBILITY_PRIVATE = 'P';
    const VISIBILITY_PUBLIC = 'X';
    const VISIBILITY_ASSIGNEE = 'A';
    const VISIBILITY_INVOLVED = 'I';

    const PROGRESS_CALCULATION_OWN = 'O';
    const PROGRESS_CALCULATION_CHECKLIST = 'C';
    const PROGRESS_CALCULATION_SUBTASK = 'S';

    const TIMER_TYPE_GLOBAL = 'G';
    const TIMER_TYPE_INDIVIDUAL = 'I';

    const EVENT_PROGRESS_UPDATED = 'eventProgressUpdated';

    public $assignee_ids = [];
    public $checklists = [];
    public $uploaded_attachments;

    /** @var bool Wether the task updated from interaction or not */
    public $_fromInteraction = false;

    /** @var bool used to flag wether progress already saved (prevent infinite loop) */
    protected $_progressSaved = false;

    /** @var TaskInteraction */
    protected $_currentInteraction;

    protected $_relatedModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['title', 'status_id'],
                'required',
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
            [
                ['price', 'estimation'],
                'number',
                'min' => 0,
            ],
            [
                'estimation',
                'default',
                'value' => null,
            ],
            [
                ['estimation_modifier', 'timer_type', 'price_modifier'],
                'string',
                'max' => 1,
            ],
            [
                ['estimation_modifier'],
                'in',
                'range' => array_keys(self::estimationModifiers()),
            ],
            [
                ['visibility'],
                'in',
                'range' => array_keys(self::visibilities()),
            ],
            [
                ['progress_calculation'],
                'in',
                'range' => array_keys(self::progressCalculations()),
            ],
            [
                [
                    'is_notified_when_progress_updated',
                    'is_comment_allowed',
                    'is_customer_allowed_to_comment',
                    'is_notified_only_when_customer_comment',
                    'is_notified_when_comment',
                    'is_timer_enabled',
                ],
                'boolean',
            ],
            [
                ['creator_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Staff::class,
                'targetAttribute' => ['creator_id' => 'id'],
            ],
            [
                ['parent_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Task::class,
                'targetAttribute' => ['parent_id' => 'id'],
            ],
            [
                ['priority_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => TaskPriority::class,
                'targetAttribute' => ['priority_id' => 'id'],
                'filter' => function ($query) {
                    /** @var TaskPriorityQuery $query */

                    return $query->enabled();
                },
            ],
            [
                ['status_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => TaskStatus::class,
                'targetAttribute' => ['status_id' => 'id'],
                'filter' => function ($query) {
                    /** @var TaskStatusQuery $query */

                    return $query->enabled();
                },
            ],
            [
                ['assignee_ids'],
                'exist',
                'skipOnError' => true,
                'allowArray' => true,
                'targetAttribute' => 'id',
                'targetClass' => Staff::class,
            ],
            [
                'uploaded_attachments',
                'each',
                'rule' => [
                    'file',
                ],
            ],
            [
                'model_id',
                'required',
                'when' => function ($model) {
                    return !empty($model->model);
                },
            ],
            [
                'model',
                'in',
                'range' => array_keys(TaskRelation::map()),
            ],
            [
                'model_id',
                'validateRelatedModel',
            ],
            [
                ['started_date', 'deadline_date', 'description', 'checklists'],
                'safe',
            ],
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    public function validateRelatedModel()
    {
        if ($this->hasErrors() || empty($this->model)) {
            return;
        }

        $relation = TaskRelation::get($this->model);

        $relation->validate($this->getRelatedModel(), $this);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'parent_id' => Yii::t('app', 'Parent'),
            'model' => Yii::t('app', 'Related to'),
            'model_id' => Yii::t('app', 'Related to'),
            'status_id' => Yii::t('app', 'Status'),
            'priority_id' => Yii::t('app', 'Priority'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'started_date' => Yii::t('app', 'Started Date'),
            'deadline_date' => Yii::t('app', 'Deadline Date'),
            'progress' => Yii::t('app', 'Progress'),
            'estimation' => Yii::t('app', 'Estimation'),
            'estimation_modifier' => Yii::t('app', 'Estimation Modifier'),
            'is_timer_enabled' => Yii::t('app', 'Enable Timer'),
            'timer_type' => Yii::t('app', 'Timer Type'),
            'is_timer_active' => Yii::t('app', 'Is Timer Active'),
            'is_billable' => Yii::t('app', 'Is Billable'),
            'is_archieved' => Yii::t('app', 'Is Archieved'),
            'price' => Yii::t('app', 'Price'),
            'price_modifier' => Yii::t('app', 'Price Modifier'),
            'is_internal' => Yii::t('app', 'Is Internal'),
            'is_notified_when_progress_updated' => Yii::t('app', 'Notify when progress updated'),
            'is_comment_allowed' => Yii::t('app', 'Allow comment'),
            'is_customer_allowed_to_comment' => Yii::t('app', 'Allow customer to comment'),
            'is_notified_when_comment' => Yii::t('app', 'Notify when someone post comment'),
            'is_notified_only_when_customer_comment' => Yii::t('app', 'Notify only when customer post comment'),
            'visibility' => Yii::t('app', 'Visibility'),
            'creator' => Yii::t('app', 'Creator'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'assignee_ids' => Yii::t('app', 'Assign to'),
            'uploaded_attachments' => Yii::t('app', 'Attachment'),
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
        ];

        $behaviors['attributeTypecast'] = [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'status_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                'priority_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                'started_date' => AttributeTypecastBehavior::TYPE_INTEGER,
                'deadline_date' => AttributeTypecastBehavior::TYPE_INTEGER,
                'is_timer_enabled' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                'is_comment_allowed' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                'is_customer_allowed_to_comment' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                'is_notified_when_comment' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                'estimation' => AttributeTypecastBehavior::TYPE_FLOAT,
                'is_notified_when_progress_updated' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                'is_notified_only_when_customer_comment' => AttributeTypecastBehavior::TYPE_BOOLEAN,
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['admin/add'] = $scenarios['default'];
        $scenarios['admin/update'] = $scenarios['admin/add'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        $transactions = parent::transactions();

        $transactions['default'] = self::OP_ALL;
        $transactions['admin/add'] = self::OP_ALL;
        $transactions['admin/update'] = self::OP_ALL;

        return $transactions;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Stop all active timer when user disable timer
        if (
            in_array($this->scenario, ['admin/add', 'admin/update']) &&
            $this->isAttributeChanged('is_timer_enabled') &&
            $this->getOldAttribute('is_timer_enabled') &&
            !$this->is_timer_enabled
        ) {
            foreach ($this->getTimers()->started()->all() AS $activeTimer) {
                /** @var StaffAccount $account */
                $account = Yii::$app->user->identity;

                $activeTimer->stop($account->profile->id);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Save attachments
        if ($this->uploaded_attachments) {
            if (!$this->saveAttachments()) {
                throw new DbException('Failed to save Attachment');
            }
        }

        $isManualUpdate = in_array($this->scenario, ['admin/add', 'admin/update']);
        $realChangedAttributes = $changedAttributes;

        unset(
            $realChangedAttributes['assignee_ids'],
            $realChangedAttributes['status_id'],
            $realChangedAttributes['priority_id'],
            $realChangedAttributes['updated_at'],
        );

        // Set History
        if (
            $isManualUpdate &&
            !empty($realChangedAttributes) &&
            $this->_progressSaved === false
        ) {
            $this->recordSavedHistory($insert);
        }

        // Set changed status history
        if (array_key_exists('status_id', $changedAttributes)) {
            $this->recordStatusChangedHistory();
        }

        // Set changed priority history
        if (array_key_exists('priority_id', $changedAttributes)) {
            $this->recordPriorityChangedHistory();
        }

        $currentInteraction = $this->getCurrentInteraction();

        if ($isManualUpdate) {
            // Stop all active timers when user disable timer
            if (!$insert && array_key_exists('is_timer_enabled', $changedAttributes) && !$this->is_timer_enabled) {
                if (!$this->stopAllTimers()) {
                    throw new DbException('Failed to stop timers');
                }
            }

            // Save assignee
            if ($this->assignee_ids) {
                if (!$this->saveAssignees()) {
                    throw new DbException('Failed to assign task');
                }

                $this->assignee_ids = $this->getAssigneesRelationship()->select('assignees_of_task.assignee_id')->createCommand()->queryColumn();
            }

            // Save checklist
            if ($this->checklists) {
                if (!$this->saveChecklists()) {
                    throw new DbException('Failed to save Checklist');
                }

                $this->checklists = ArrayHelper::index($this->getChecklists()->select(['id', 'label', 'is_checked'])->createCommand()->queryAll(), 'id');
            }

            // Update task progress
            if (!$this->_progressSaved) {
                $oldProgress = $this->progress;

                if (!$this->calculateProgress()) {
                    throw new DbException("Failed to save progress");
                }

                if ($oldProgress != $this->progress || $insert) {
                    $currentInteraction->progress = $this->progress;
                }
            }

            if (array_key_exists('status_id', $changedAttributes)) {
                $currentInteraction->status_id = $this->status_id;
            }
        }

        if (!$this->_fromInteraction && ($currentInteraction->isAttributeChanged('progress') || $currentInteraction->isAttributeChanged('status_id'))) {
            $currentInteraction->save();
        }

        parent::afterSave($insert, $changedAttributes);
    }


    /**
     * @inheritdoc
     *
     * @return TaskQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TaskQuery(get_called_class());

        return $query->alias("task");
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);

        /** @var Setting $setting */
        $setting = Yii::$app->setting;

        if (!$skipIfSet || !isset($this->started_date)) {
            $this->started_date = time();
        }

        if (!$skipIfSet || !isset($this->status_id)) {
            $this->status_id = $setting->get('task/default_status');
        }

        if (!$skipIfSet || !isset($this->priority_id)) {
            $this->priority_id = $setting->get('task/default_priority');
        }

        if (!$skipIfSet || !isset($this->visibility)) {
            $this->visibility = self::VISIBILITY_INVOLVED;
        }

        if (!$skipIfSet || !isset($this->progress_calculation)) {
            $this->progress_calculation = self::PROGRESS_CALCULATION_OWN;
        }

        if (!$skipIfSet || !isset($this->timer_type)) {
            $this->timer_type = self::TIMER_TYPE_GLOBAL;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'title', 'progress', 'status_id', 'priority_id']);

        $params['progress'] = $params['progress'] * 100;
        $params['status_label'] = $this->status->label;
        $params['priority_label'] = $this->priority->label;

        return $params;
    }

    /**
     * @return bool
     * @throws DbException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function calculateProgress()
    {
        switch ($this->progress_calculation) {
            case self::PROGRESS_CALCULATION_CHECKLIST:
                return $this->calculateProgressByChecklist();
                break;
            case self::PROGRESS_CALCULATION_SUBTASK:
                return $this->calculateProgressBySubtask();
                break;
        }

        return true;
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws StaleObjectException
     */
    protected function saveAssignees()
    {
        $currentModels = $this->getAssigneesRelationship()
            ->indexBy('assignee_id')
            ->all();

        foreach ($this->assignee_ids AS $assigneeId) {
            if (isset($currentModels[$assigneeId])) {
                continue;
            }

            if (!$this->assign($assigneeId, false)) {
                return false;
            }
        }

        $addedModels = $this->getAssigneesRelationship()
            ->andWhere(['NOT IN', 'assignee_id', array_keys($currentModels)])
            ->all();

        TaskAssignee::sendAssignNotification($addedModels, $this, $this->creator);

        foreach ($currentModels AS $key => $model) {
            if (in_array($key, $this->assignee_ids)) {
                continue;
            }

            if (!$model->delete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws Throwable
     */
    protected function saveChecklists()
    {
        $deletedModels = TaskChecklist::find()
            ->andWhere(['NOT IN', 'id', array_keys($this->checklists)])
            ->andWhere(['task_id' => $this->id])
            ->all();

        foreach ($deletedModels AS $deletedModel) {
            if (!$deletedModel->delete()) {
                return false;
            }
        }

        foreach ($this->checklists AS $id => $data) {
            if (strpos($id, '__') === 0) {
                $data['task_id'] = $this->id;

                $model = new TaskChecklist($data);
                $model->scenario = 'admin/task/add';
            } else {
                $model = TaskChecklist::find()->andWhere(['id' => $id, 'task_id' => $this->id])->one();

                if (!$model) {
                    continue;
                }

                $model->scenario = 'admin/task/update';
                $model->setAttributes($data);
            }

            if (!$model->save()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function saveAttachments()
    {
        foreach ($this->uploaded_attachments AS $attachment) {
            $model = new TaskAttachment([
                'uploaded_file' => $attachment,
                'task_id' => $this->id,
            ]);

            if (!$model->save()) {
                return false;
            }
        }

        $this->uploaded_attachments = [];

        return true;
    }

    /**
     * @param Staff|int $staff
     * @param bool      $notify
     *
     * @return bool
     *
     * @throws InvalidConfigException
     */
    public function assign($staff, $notify = true)
    {
        $staffId = $staff instanceof Staff ? $staff->id : $staff;

        $model = new TaskAssignee([
            'scenario' => 'admin/task/add',
            'task_id' => $this->id,
            'assignee_id' => $staffId,
            'assignor_id' => $this->creator_id,
        ]);

        $model->loadDefaultValues();

        if (!$model->save()) {
            return false;
        }

        if ($notify) {
            TaskAssignee::sendAssignNotification([$model], $this, $this->creator);
        }

        return true;
    }

    /**
     * @param $staff
     *
     * @return bool|false|int
     *
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function unassign($staff)
    {
        $staffId = $staff instanceof Staff ? $staff->id : $staff;

        $model = $this->getAssigneesRelationship()->andWhere(['assignee_id' => $staffId])->one();

        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    /**
     * @param bool|string $timerType
     *
     * @return array|string|null
     */
    public static function timerTypes($timerType = false)
    {
        $timerTypes = [
            self::TIMER_TYPE_GLOBAL => Yii::t('app', 'Global (One timer for all assignee)'),
            self::TIMER_TYPE_INDIVIDUAL => Yii::t('app', 'Individual (Each assignee can start/stop their own timer)'),
        ];

        if ($timerType !== false) {
            return isset($timerTypes[$timerType]) ? $timerTypes[$timerType] : null;
        }

        return $timerTypes;
    }

    /**
     * @return null|string
     */
    public function getTimerType()
    {
        return self::timerTypes($this->timer_type);
    }

    /**
     * @param bool|string $progressCalculation
     *
     * @return array|string|null
     */
    public static function progressCalculations($progressCalculation = false)
    {
        $progressCalculations = [
            self::PROGRESS_CALCULATION_OWN => Yii::t('app', 'Has it\'s own progress'),
            self::PROGRESS_CALCULATION_SUBTASK => Yii::t('app', 'Calculate progress from it\'s subtask'),
            self::PROGRESS_CALCULATION_CHECKLIST => Yii::t('app', 'Calculate progress from it\'s checklist'),
        ];

        if ($progressCalculation !== false) {
            return isset($progressCalculations[$progressCalculation]) ? $progressCalculations[$progressCalculation] : null;
        }

        return $progressCalculations;
    }

    /**
     * @param bool|string $progressCalculation
     *
     * @return array|string|null
     */
    public static function progressCalculationsDisplay($progressCalculation = false)
    {
        $progressCalculations = [
            self::PROGRESS_CALCULATION_OWN => Yii::t('app', 'Calculated'),
            self::PROGRESS_CALCULATION_SUBTASK => Yii::t('app', 'Calculated from subtask'),
            self::PROGRESS_CALCULATION_CHECKLIST => Yii::t('app', 'Calculated from checklist'),
        ];

        if ($progressCalculation !== false) {
            return isset($progressCalculations[$progressCalculation]) ? $progressCalculations[$progressCalculation] : null;
        }

        return $progressCalculations;
    }

    /**
     * @return null|string
     */
    public function getProgressCalculationText()
    {
        return self::progressCalculations($this->progress_calculation);
    }

    /**
     * @return null|string
     */
    public function getProgressCalculationDisplay()
    {
        return self::progressCalculationsDisplay($this->progress_calculation);
    }

    /**
     * @param bool|string $modifier
     *
     * @return array|string|null
     */
    public static function estimationModifiers($modifier = false)
    {
        $modifiers = [
            self::ESTIMATION_MODIFIER_HOUR => Yii::t('app', 'Hour'),
            self::ESTIMATION_MODIFIER_DAY => Yii::t('app', 'Day'),
            self::ESTIMATION_MODIFIER_MONTH => Yii::t('app', 'Month'),
        ];

        if ($modifier !== false) {
            return isset($modifiers[$modifier]) ? $modifiers[$modifier] : null;
        }

        return $modifiers;
    }

    /**
     * @return null|string
     */
    public function getEstimationModifierText()
    {
        return self::estimationModifiers($this->estimation_modifier);
    }

    /**
     * @param bool|string $visibility
     *
     * @return array|string|null
     */
    public static function visibilities($visibility = false)
    {
        $visibilities = [
            self::VISIBILITY_INVOLVED => Yii::t('app', 'Visible to assignee, customer and you'),
            self::VISIBILITY_ASSIGNEE => Yii::t('app', 'Visible only to assignee and you'),
            self::VISIBILITY_PUBLIC => Yii::t('app', 'Visible to everyone, including customer'),
            self::VISIBILITY_PRIVATE => Yii::t('app', 'Visible only to you'),
        ];

        if ($visibility !== false) {
            return isset($visibilities[$visibility]) ? $visibilities[$visibility] : null;
        }

        return $visibilities;
    }

    /**
     * @return null|string
     */
    public function getVisibilityText()
    {
        return self::visibilities($this->visibility);
    }

    /**
     * @return ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(Staff::class, ['id' => 'creator_id'])->alias('creator_of_task');
    }

    /**
     * @return ActiveQuery|TaskQuery
     */
    public function getParent()
    {
        return $this->hasOne(Task::class, ['id' => 'parent_id'])->alias('parent_of_task');
    }

    /**
     * @return ActiveQuery|TaskQuery
     */
    public function getChildren()
    {
        return $this->hasMany(Task::class, ['parent_id' => 'id'])->alias('children_of_task');
    }

    /**
     * @return ActiveQuery|TaskPriorityQuery
     */
    public function getPriority()
    {
        return $this->hasOne(TaskPriority::class, ['id' => 'priority_id'])->alias('priority_of_task');
    }

    /**
     * @return ActiveQuery|TaskStatusQuery
     */
    public function getStatus()
    {
        return $this->hasOne(TaskStatus::class, ['id' => 'status_id'])->alias('status_of_task');
    }

    /**
     * @return ActiveQuery|TaskChecklistQuery
     */
    public function getChecklists()
    {
        return $this->hasMany(TaskChecklist::class, ['task_id' => 'id'])->alias('checklists_of_task');
    }

    /**
     * @return TaskRelation|null
     *
     * @throws InvalidConfigException
     */
    public function getRelatedObject()
    {
        if (empty($this->model)) {
            return null;
        }

        return TaskRelation::get($this->model);
    }

    /**
     * @return mixed|null
     * @throws InvalidConfigException
     */
    public function getRelatedModel()
    {
        if (empty($this->model)) {
            return null;
        }

        if (!isset($this->_relatedModel)) {
            $this->_relatedModel = $this->getRelatedObject()->getModel($this->model_id);
        }

        return $this->_relatedModel;
    }

    /**
     * @return ActiveQuery|TaskTimerQuery
     */
    public function getTimers()
    {
        return $this->hasMany(TaskTimer::class, ['task_id' => 'id'])->alias('timers_of_task');
    }

    /**
     * @return string|float|int
     */
    public function getTotalRecordedTime()
    {
        return $this->getTimers()->stopped()->sum('timers_of_task.stopped_at - timers_of_task.started_at');
    }

    /**
     * @return ActiveQuery|TaskAssigneeQuery
     */
    public function getAssigneesRelationship()
    {
        return $this->hasMany(TaskAssignee::class, ['task_id' => 'id'])->alias('assignees_of_task');
    }

    /**
     * @return ActiveQuery|StaffQuery
     */
    public function getAssignees()
    {
        return $this->hasMany(Staff::class, ['id' => 'assignee_id'])->via('assigneesRelationship');
    }

    /**
     * @return ActiveQuery
     */
    public function getFollowersRelationship()
    {
        return $this->hasMany(TaskFollower::class, ['task_id' => 'id'])->alias('followers_of_task');
    }

    /**
     * @return bool
     */
    public function getIsOverdue()
    {
        return $this->deadline_date <= time();
    }

    /**
     * @return bool
     */
    public function getIsStarted()
    {
        return $this->started_date <= time();
    }

    /**
     * @return ActiveQuery|TaskAttachmentQuery
     */
    public function getAttachments()
    {
        return $this->hasMany(TaskAttachment::class, ['task_id' => 'id'])->alias('attachments_of_task');
    }

    /**
     * @param integer $statusId
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function changeStatus($statusId)
    {
        if (!TaskStatus::find()->andWhere(['id' => $statusId])->enabled()->exists()) {
            $this->addError('status_id', Yii::t('app', '{object} doesn\'t exists', [
                'object' => Yii::t('app', 'Status'),
            ]));

            return false;
        }

        if ($this->status_id == $statusId) {
            return true;
        }

        $this->status_id = $statusId;

        if (!$this->_fromInteraction) {
            $this->getCurrentInteraction()->status_id = $this->status_id;
        }

        if (!$this->save(false)) {
            return false;
        }

        return true;
    }

    /**
     * @param int|string     $statusId
     * @param int[]|string[] $taskIds
     *
     * @return bool
     *
     * @throws DbException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function changeStatuses($statusId, $taskIds)
    {
        if (is_string($taskIds)) {
            $taskIds = explode(',', $taskIds);
        }

        if (empty($taskIds)) {
            return true;
        }

        $tasks = Task::find()->andWhere(['id' => $taskIds])
            ->andWhere(['!=', 'status_id', $statusId])
            ->all();

        $transaction = self::getDb()->beginTransaction();

        try {
            foreach ($tasks AS $task) {
                if ($task->changeStatus($statusId)) {
                    continue;
                }

                $transaction->rollBack();

                return false;
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
     * @param integer $priorityId
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function changePriority($priorityId)
    {
        if (!TaskPriority::find()->andWhere(['id' => $priorityId])->enabled()->exists()) {
            $this->addError('status_id', Yii::t('app', '{object} doesn\'t exists', [
                'object' => Yii::t('app', 'Priority'),
            ]));

            return false;
        }

        if ($this->priority_id == $priorityId) {
            return true;
        }

        $this->priority_id = $priorityId;

        return $this->save(false);
    }


    /**
     * @param int|string     $priorityId
     * @param int[]|string[] $taskIds
     *
     * @return bool
     *
     * @throws DbException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function changePriorities($priorityId, $taskIds)
    {
        if (is_string($taskIds)) {
            $taskIds = explode(',', $taskIds);
        }

        if (empty($taskIds)) {
            return true;
        }

        $tasks = Task::find()->andWhere(['id' => $taskIds])
            ->andWhere(['!=', 'priority_id', $priorityId])
            ->all();

        $transaction = self::getDb()->beginTransaction();

        try {
            foreach ($tasks AS $task) {
                if ($task->changePriority($priorityId)) {
                    continue;
                }

                $transaction->rollBack();

                return false;
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
     * @return bool
     */
    public function sendProgressNotification()
    {
        return true;
    }

    /**
     * @param float $progress
     *
     * @return bool
     * @throws DbException
     * @throws Throwable
     */
    public function updateProgress($progress)
    {
        $progress = round($progress, 3);

        $this->_progressSaved = true;

        if ($this->progress == $progress) {
            return true;
        }

        $this->progress = $progress;

        if (!$this->_fromInteraction) {
            $this->getCurrentInteraction()->progress = $this->progress;
        }

        if (!$this->save(false)) {
            return false;
        }

        $this->sendProgressNotification();
        $this->recordProgressChangedHistory();

        $this->trigger(self::EVENT_PROGRESS_UPDATED);

        return true;
    }

    /**
     * @param null|integer $starterId
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function isTimerStarted($starterId = null)
    {
        $isTimesStartedQuery = TaskTimer::find()->andWhere(['task_id' => $this->id])->started();

        if ($this->timer_type === self::TIMER_TYPE_INDIVIDUAL && !is_null($starterId)) {
            $isTimesStartedQuery->andWhere(['starter_id' => $starterId]);
        }

        return $isTimesStartedQuery->exists();
    }

    /**
     * @param null|integer $starterId
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function startTimer($starterId = null)
    {
        if (!$this->is_timer_enabled) {
            return false;
        }

        if ($this->isTimerStarted($starterId)) {
            return true;
        }

        $timer = new TaskTimer([
            'task_id' => $this->id,
            'starter_id' => $starterId,
            'scenario' => 'admin/start',
        ]);

        return $timer->save();
    }

    /**
     * @param integer $stopperId
     *
     * @return bool
     * @throws InvalidConfigException
     * @throws DbException
     */
    public function stopTimer($stopperId)
    {
        if (!$this->isTimerStarted($stopperId)) {
            $this->addError('is_timer_active', Yii::t('app', 'There are no timer active'));

            return false;
        }

        $timerQuery = $this->getTimers()->started();

        if ($this->timer_type === self::TIMER_TYPE_INDIVIDUAL) {
            $timerQuery->andWhere(['starter_id' => $stopperId]);
        }

        $timer = $timerQuery->one();

        if (!$timer) {
            throw new Exception("Can't find timer");
        }

        $timer->scenario = 'admin/stop';

        return $timer->stop($stopperId);
    }

    public function stopAllTimers()
    {
        $timers = $this->getTimers()->started()->all();
        $transaction = TaskTimer::getDb()->beginTransaction();

        try {
            foreach ($timers AS $timer) {
                $timer->scenario = 'admin/stop';

                if (!$timer->stop($this->creator_id)) {
                    $transaction->rollBack();

                    return false;
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @return bool
     * @throws DbException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    protected function calculateProgressByChecklist()
    {
        $query = TaskChecklist::find()->andWhere(['task_checklist.task_id' => $this->id]);
        $checkedQuery = clone $query;

        $checkedQuery->checked();

        $total = $query->count();
        $totalChecked = $checkedQuery->count();

        return $this->updateProgress($total > 0 ? $totalChecked / $total : 0);
    }

    /**
     * @param int $starterId
     *
     * @return TaskTimer|null
     * @throws InvalidConfigException
     */
    public function getActiveTimer($starterId)
    {
        $query = TaskTimer::find()->andWhere(['task_timer.task_id' => $this->id, 'task_timer.stopped_at' => null]);

        if ($this->timer_type == self::TIMER_TYPE_INDIVIDUAL && $starterId) {
            $query->andWhere(['task_timer.starter_id' => $starterId]);
        }

        return $query->one();
    }

    /**
     * @return float|int
     */
    public function getEstimationSecond()
    {
        if (!$this->estimation) {
            return false;
        }

        switch ($this->estimation_modifier) {
            case self::ESTIMATION_MODIFIER_MONTH:
                return $this->estimation * 155520000; // Convert month to seconds (assuming that 1 month is 30 days)
            case self::ESTIMATION_MODIFIER_DAY:
                return $this->estimation * 86400; // Convert day to seconds
        }

        return $this->estimation * 3600; // Convert hour to seconds
    }

    /**
     * @return bool|TaskInteraction
     */
    protected function getCurrentInteraction()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        if (!$this->_currentInteraction) {
            /** @var StaffAccount $account */
            $account = Yii::$app->user->identity;

            $this->_currentInteraction = new TaskInteraction([
                'scenario' => 'admin/task/add',
                'staff_id' => $account->profile->id,
                'task_id' => $this->id,
            ]);
        }

        return $this->_currentInteraction;
    }

    /**
     * @return bool
     */
    protected function calculateProgressBySubtask()
    {
        return true;
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
            'model' => self::class,
            'model_id' => $this->id,
        ];

        if ($this->scenario === 'admin/add' && $insert) {
            $history['description'] = 'Adding task "{title}"';
        } else {
            $history['description'] = 'Updating task "{title}"';
        }

        $historyEvent = $this->scenario === 'admin/add' ? 'task.add' : 'task.update';
        $history['tag'] = $this->scenario === 'admin/add' ? 'add' : 'update';

        return Account::history()->save($historyEvent, $history);
    }

    /**
     * @return bool
     * @throws DbException
     * @throws Throwable
     */
    public function recordStatusChangedHistory()
    {
        return Account::history()->save('task.status', [
            'params' => $this->getHistoryParams(),
            'description' => 'Changing status of task "{title}" to {status_label}',
            'tag' => 'update',
            'model' => self::class,
            'model_id' => $this->id,
        ]);
    }

    /**
     * @return bool
     * @throws DbException
     * @throws Throwable
     */
    public function recordPriorityChangedHistory()
    {
        $historyRelation = [
            Task::class => $this->id,
        ];

        if (!empty($this->model)) {
            $historyRelation[get_class($this->getRelatedModel())] = $this->model_id;
        }

        return Account::history()->save('task.priority', [
            'params' => $this->getHistoryParams(),
            'description' => 'Changing priority of task "{title}" to {priority_label}',
            'tag' => 'update',
            'model' => self::class,
            'model_id' => $this->id,
        ]);
    }

    /**
     * @return bool
     * @throws DbException
     * @throws Throwable
     */
    public function recordProgressChangedHistory()
    {
        return Account::history()->save('task.progress', [
            'params' => $this->getHistoryParams(),
            'description' => 'Set progress of task "{title}" to {progress}%',
            'tag' => 'update',
            'model' => self::class,
            'model_id' => $this->id,
        ]);
    }
}
