<?php namespace modules\project\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\account\models\queries\StaffQuery;
use modules\account\models\Staff;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\validators\DateValidator;
use modules\crm\models\Customer;
use modules\crm\models\queries\CustomerQuery;
use modules\finance\models\Currency;
use modules\finance\models\Invoice;
use modules\finance\models\queries\CurrencyQuery;
use modules\finance\models\queries\InvoiceQuery;
use modules\project\models\queries\ProjectMemberQuery;
use modules\project\models\queries\ProjectMilestoneQuery;
use modules\project\models\queries\ProjectQuery;
use modules\project\models\queries\ProjectStatusQuery;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as DbException;
use yii\db\StaleObjectException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Customer                  $customer
 * @property Currency                  $currency
 * @property ProjectStatus             $status
 * @property null|string               $visibilityText
 * @property Invoice[]|array           $invoices
 * @property ProjectAttachment[]|array $attachments
 * @property ProjectMilestone[]|array  $milestones
 * @property Staff[]                   $members
 * @property ProjectMember[]           $membersRelationship
 * @property bool                      $isStarted
 * @property bool                      $isOverdue
 * @property array                     $historyParams
 * @property string|float|int          $totalDue
 *
 * @property int                       $id                                [int(10) unsigned]
 * @property int                       $customer_id                       [int(11) unsigned]
 * @property int                       $status_id                         [int(11) unsigned]
 * @property string                    $currency_code                     [char(3)]
 * @property string                    $name
 * @property string                    $description
 * @property string                    $progress                          [decimal(5,4)]
 * @property bool                      $is_progress_calcuted_through_task [tinyint(1)]
 * @property string                    $budget                            [decimal(10)]
 * @property int                       $started_date                      [int(11) unsigned]
 * @property int                       $deadline_date                     [int(11) unsigned]
 * @property string                    $visibility                        [char(1)]
 * @property bool                      $is_visible_to_customer            [tinyint(1)]
 * @property int                       $created_at                        [int(11) unsigned]
 * @property int                       $updated_at                        [int(11) unsigned]
 */
class Project extends ActiveRecord
{
    const VISIBILITY_INVOLVED = 'I';
    const VISIBILITY_STAFF = 'S';

    public $uploaded_attachments = [];
    public $member_ids = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%project}}';
    }

    /**
     * @inheritdoc
     *
     * @return ProjectQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new ProjectQuery(get_called_class());

        return $query->alias("project");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['name', 'status_id', 'customer_id', 'started_date'],
                'required',
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                'visibility',
                'in',
                'range' => array_keys(self::visibilities()),
            ],
            [
                'budget',
                'double',
                'min' => 0,
            ],
            [
                'uploaded_attachments',
                'each',
                'rule' => [
                    'file',
                ],
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
                'customer_id',
                'exist',
                'targetRelation' => 'customer',
            ],
            [
                'status_id',
                'exist',
                'targetRelation' => 'status',
            ],
            [
                'currency_code',
                'exist',
                'targetRelation' => 'currency',
            ],
            [
                ['description', 'name'],
                'string',
            ],
            [
                ['member_ids'],
                'exist',
                'skipOnError' => true,
                'allowArray' => true,
                'targetAttribute' => 'id',
                'targetClass' => Staff::class,
            ],
            [
                ['started_date', 'deadline_date'],
                'safe',
            ],
            [
                ['is_progress_calcuted_through_task', 'is_visible_to_customer'],
                'boolean',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        return [
            'admin/add' => self::OP_ALL,
            'admin/update' => self::OP_ALL,
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

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {

        $isManualUpdate = in_array($this->scenario, ['admin/add', 'admin/update']);

        // Save attachments
        if ($this->uploaded_attachments) {
            if (!$this->saveAttachments()) {
                throw new DbException('Failed to save Attachment');
            }
        }

        // Save assignee
        if ($this->member_ids && $isManualUpdate) {
            if (!$this->saveMembers()) {
                throw new DbException('Failed to assign task');
            }

            $this->member_ids = $this->getMembersRelationship()->select('members_of_project.staff_id')->createCommand()->queryColumn();

        }

        // Set History
        if (
            in_array($this->scenario, ['admin/add', 'admin/update']) &&
            !empty($changedAttributes)
        ) {
            $this->recordSavedHistory($insert);
        }

        // Set changed status history
        if (array_key_exists('status_id', $changedAttributes)) {
            $this->recordStatusChangedHistory();
        }

        parent::afterSave($insert, $changedAttributes);
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
     * @return ActiveQuery|ProjectQuery
     */
    public function getAttachments()
    {
        return $this->hasMany(ProjectAttachment::class, ['project_id' => 'id'])->alias('attachments_of_project');
    }

    /**
     * @return bool
     */
    protected function saveAttachments()
    {
        foreach ($this->uploaded_attachments AS $attachment) {
            $model = new ProjectAttachment([
                'uploaded_file' => $attachment,
                'project_id' => $this->id,
            ]);

            if (!$model->save()) {
                return false;
            }
        }

        $this->uploaded_attachments = [];

        return true;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', 'Customer'),
            'status_id' => Yii::t('app', 'Status'),
            'member_ids' => Yii::t('app', 'Member'),
            'currency_code' => Yii::t('app', 'Currency'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'progress' => Yii::t('app', 'Progress'),
            'is_progress_calcuted_through_task' => Yii::t('app', 'Is Progress Calcuted Through Task'),
            'budget' => Yii::t('app', 'Budget'),
            'start_date' => Yii::t('app', 'Start Date'),
            'deadline_date' => Yii::t('app', 'Deadline Date'),
            'visibility' => Yii::t('app', 'Visibility'),
            'is_visible_to_customer' => Yii::t('app', 'Visible to Customer'),
            'uploaded_attachments' => Yii::t('app', 'Attachments'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @param bool $visibility
     *
     * @return array|string|null
     */
    public static function visibilities($visibility = false)
    {
        $visibilities = [
            self::VISIBILITY_STAFF => Yii::t('app', 'Visible to all staff'),
            self::VISIBILITY_INVOLVED => Yii::t('app', 'Visible only to involved staff'),
        ];

        if ($visibility !== false) {
            return isset($visibilities[$visibility]) ? $visibilities[$visibility] : null;
        }

        return $visibilities;
    }

    /**
     * @return string|null
     */
    public function getVisibilityText()
    {
        return self::visibilities($this->visibility);
    }

    /**
     * @return ActiveQuery|CustomerQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id'])->alias('customer_of_project');
    }

    /**
     * @return ActiveQuery|ProjectStatusQuery
     */
    public function getStatus()
    {
        return $this->hasOne(ProjectStatus::class, ['id' => 'status_id'])->alias('status_of_project');
    }

    /**
     * @return ActiveQuery|CurrencyQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['code' => 'currency_code'])->alias('currency_of_project');
    }

    /**
     * @return ActiveQuery|InvoiceQuery
     */
    public function getInvoices()
    {
        return $this->hasMany(Invoice::class, ['id' => 'invoice_id'])->alias('invoices_of_project');
    }

    /**
     * @return ActiveQuery|ProjectMilestoneQuery
     */
    public function getMilestones()
    {
        return $this->hasMany(ProjectMilestone::class, ['project_id' => 'id'])->alias('milestones_of_project');
    }

    /**
     * @return ActiveQuery|StaffQuery
     */
    public function getMembers()
    {
        return $this->hasMany(Staff::class, ['id' => 'assignee_id'])->via('membersRelationship');
    }

    /**
     * @return string|float|int
     */
    public function getTotalDue()
    {
        return $this->getInvoices()->sum('real_total_due');
    }

    /**
     * @return ActiveQuery|ProjectMemberQuery
     */
    public function getMembersRelationship()
    {
        return $this->hasMany(ProjectMember::class, ['project_id' => 'id'])->alias('members_of_project');
    }

    /**
     * @param integer $statusId
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function changeStatus($statusId)
    {
        if (!ProjectStatus::find()->andWhere(['id' => $statusId])->enabled()->exists()) {
            $this->addError('status_id', Yii::t('app', '{object} doesn\'t exists', [
                'object' => Yii::t('app', 'Status'),
            ]));

            return false;
        }

        if ($this->status_id == $statusId) {
            return true;
        }

        $this->status_id = $statusId;

        if (!$this->save(false)) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'customer_id', 'name']);

        $params['customer_name'] = $this->customer->name;
        $params['status_id'] = $this->status_id;
        $params['status_label'] = $this->status->label;

        return $params;
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
            'model_id' => $this->id
        ];

        if ($this->scenario === 'admin/add' && $insert) {
            $history['description'] = 'Adding project "{name}" to customer "{customer_name}"';
        } else {
            $history['description'] = 'Updating {customer_name}\'s project "{name}"';
        }

        $historyEvent = $this->scenario === 'admin/add' ? 'project.add' : 'project.update';
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
        return Account::history()->save('project.status', [
            'params' => $this->getHistoryParams(),
            'description' => 'Changing status of project "{name}" to {status_label}',
            'tag' => 'update',
            'model' => self::class,
            'model_id' => $this->id,
        ]);
    }

    /**
     * @return bool
     *
     * @throws Throwable
     * @throws StaleObjectException
     */
    protected function saveMembers()
    {
        /** @var ProjectMember[] $currentModels */
        $currentModels = $this->getMembersRelationship()->indexBy('staff_id')->all();

        $addedMembers = [];

        foreach ($this->member_ids AS $assigneeId) {
            if (isset($currentModels[$assigneeId])) {
                continue;
            }

            $model = new ProjectMember([
                'scenario' => 'admin/project/add',
                'project_id' => $this->id,
                'staff_id' => $assigneeId,
            ]);

            $model->loadDefaultValues();

            if (!$model->save()) {
                return false;
            }

            $addedMembers[] = $model;
        }

        foreach ($currentModels AS $key => $model) {
            if (in_array($key, $this->member_ids)) {
                continue;
            }

            if (!$model->delete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        if (!isset($this->currency_code) || !$skipIfSet) {
            $baseCurrency = Yii::$app->setting->get('finance/base_currency');

            $this->currency_code = $this->customer_id ? $this->customer->currency_code : $baseCurrency;
        }

        return parent::loadDefaultValues($skipIfSet);
    }
}
