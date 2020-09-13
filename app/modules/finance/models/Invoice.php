<?php namespace modules\finance\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\crm\models\Customer;
use modules\crm\models\queries\CustomerQuery;
use modules\finance\components\Payment;
use modules\finance\models\queries\CurrencyQuery;
use modules\finance\models\queries\InvoiceAssigneeQuery;
use modules\finance\models\queries\InvoiceItemQuery;
use modules\finance\models\queries\InvoicePaymentQuery;
use modules\finance\models\queries\InvoiceQuery;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as DbException;
use yii\db\StaleObjectException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Customer                $customer
 * @property Currency                $currency
 * @property bool                    $isPastDue
 * @property InvoiceAssignee[]|array $assigneesRelationship
 * @property Staff[]|array           $assignees
 * @property InvoiceItem[] |array    $items
 * @property InvoicePayment[]|array  $payments
 *
 * @property int                     $id                                  [int(10) unsigned]
 * @property int                     $customer_id                         [int(11) unsigned]
 * @property string                  $currency_code                       [char(3)]
 * @property string                  $number                              [varchar(255)]
 * @property string                  $status                              [char(1)]
 * @property int                     $date                                [int(11) unsigned]
 * @property int                     $due_date                            [int(11) unsigned]
 * @property string                  $currency_rate                       [decimal(25,10)]
 * @property string                  $sub_total                           [decimal(25,10)]
 * @property string                  $discount                            [decimal(25,10)]
 * @property string                  $tax                                 [decimal(25,10)]
 * @property string                  $grand_total                         [decimal(25,10)]
 * @property string                  $total_paid                          [decimal(25,10)]
 * @property string                  $total_due                           [decimal(25,10)]
 * @property string                  $real_sub_total                      [decimal(25,10)]
 * @property string                  $real_discount                       [decimal(25,10)]
 * @property string                  $real_tax                            [decimal(25,10)]
 * @property string                  $real_grand_total                    [decimal(25,10)]
 * @property string                  $real_total_paid                     [decimal(25,10)]
 * @property string                  $real_total_due                      [decimal(25,10)]
 * @property bool                    $is_assignee_allowed_to_add_payment  [tinyint(1)]
 * @property bool                    $is_assignee_allowed_to_add_discount [tinyint(1)]
 * @property bool                    $is_assignee_allowed_to_update_item  [tinyint(1)]
 * @property bool                    $is_assignee_allowed_to_cancel       [tinyint(1)]
 * @property bool                    $is_published                        [tinyint(1)]
 * @property bool                    $is_paid                             [tinyint(1)]
 * @property string                  $allowed_payment_method
 * @property string                  $params
 * @property int                     $created_at                          [int(11) unsigned]
 * @property int                     $updated_at                          [int(11) unsigned]
 * @property int                     $project_id                          [int(11) unsigned]
 */
class Invoice extends ActiveRecord
{
    public $assignee_ids = [];

    /** @var array|InvoiceItem[] */
    public $itemModels = [];

    protected $_items;
    protected $_recalculated = false;

    const STATUS_DRAFT = 'D';
    const STATUS_PUBLISHED = 'P';
    const STATUS_CLOSED = 'X';
    const STATUS_CANCELLED = 'C';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invoice}}';
    }

    /**
     * @inheritdoc
     * @return InvoiceQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new InvoiceQuery(get_called_class());

        return $query->alias("invoice");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['number', 'date', 'customer_id', 'currency_code'],
                'required',
                'on' => ['admin/add', 'admin/update', 'admin/temp'],
            ],
            [
                ['date', 'due_date'],
                'date',
            ],
            [
                'customer_id',
                'exist',
                'targetRelation' => 'customer',
            ],
            [
                'currency_code',
                'exist',
                'targetRelation' => 'currency',
            ],
            [
                'currency_rate',
                'required',
                'when' => function ($model) {
                    return Yii::$app->setting->get('finance/base_currency') != $model->currency_code;
                },
            ],
            [
                'allowed_payment_method',
                'each',
                'rule' => [
                    'in',
                    'range' => array_keys(Payment::all()),
                ],
            ],
            [
                ['assignee_ids'],
                'each',
                'rule' => [
                    'exist',
                    'targetClass' => Staff::class,
                    'targetAttribute' => 'id',
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
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

        $behaviors['attributeTypecast'] = [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'date' => AttributeTypecastBehavior::TYPE_INTEGER,
                'due_date' => AttributeTypecastBehavior::TYPE_INTEGER,
                'updated_at' => AttributeTypecastBehavior::TYPE_INTEGER,
                'created_at' => AttributeTypecastBehavior::TYPE_INTEGER,

                'customer_id' => AttributeTypecastBehavior::TYPE_INTEGER,

                'is_paid' => AttributeTypecastBehavior::TYPE_BOOLEAN,

                'currency_rate' => AttributeTypecastBehavior::TYPE_FLOAT,

                'tax' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_tax' => AttributeTypecastBehavior::TYPE_FLOAT,

                'sub_total' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_sub_total' => AttributeTypecastBehavior::TYPE_FLOAT,

                'grand_total' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_grand_total' => AttributeTypecastBehavior::TYPE_FLOAT,

                'discount' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_discount' => AttributeTypecastBehavior::TYPE_FLOAT,

                'total_paid' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_total_paid' => AttributeTypecastBehavior::TYPE_FLOAT,

                'total_due' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_total_due' => AttributeTypecastBehavior::TYPE_FLOAT,
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Save assignee
        if (!empty($this->assignee_ids)) {
            if (!$this->saveAssignees()) {
                throw new DbException('Failed to assign invoice');
            }
        }

        // Save items
        if (!empty($this->itemModels)) {
            if (!$this->saveItems()) {
                throw new DbException('Failed to save items');
            }
        }

        if (!$this->_recalculated) {
            $this->_recalculated = true;

            if (!$this->save(false)) {
                throw new DbException('Failed to recalculate invoice');
            }

            if (
                in_array($this->scenario, ['admin/add', 'admin/update']) &&
                !empty($changedAttributes)
            ) {
                $this->recordSavedHistory($insert);
            }

            $this->_recalculated = false;
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritDoc
     */
    public function normalizeAttributes($save = false)
    {
        if ($save) {
            if ($this->currency_code === Yii::$app->setting->get('finance/base_currency')) {
                $this->currency_rate = 1;
            }

            $this->sub_total = $this->getItems()->sum('sub_total');
            $this->tax = $this->getItems()->sum('tax');
            $this->grand_total = $this->sub_total + $this->tax;
            $this->total_paid = $this->getPayments()->status(Payment::STATUS_ACCEPTED)->sum('amount');
            $this->total_due = $this->grand_total - $this->total_paid;

            $this->real_sub_total = $this->sub_total * $this->currency_rate;
            $this->real_tax = $this->tax * $this->currency_rate;
            $this->real_grand_total = $this->grand_total * $this->currency_rate;
            $this->real_total_paid = $this->total_paid * $this->currency_rate;
            $this->real_total_due = $this->total_due * $this->currency_rate;

            $this->is_paid = $this->total_due == 0;

            if (is_array($this->allowed_payment_method)) {
                $this->allowed_payment_method = implode(',', $this->allowed_payment_method);
            }

            $this->typecastAttributes();
        } else {
            if (in_array($this->scenario, ['admin/add', 'admin/update'])) {
                $this->assignee_ids = $this->getAssigneesRelationship()->select('assignee_id')->createCommand()->queryColumn();
            }

            if (is_string($this->allowed_payment_method)) {
                $this->allowed_payment_method = explode(',', $this->allowed_payment_method);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        if ((empty($this->assignee_ids) || !$skipIfSet) && Yii::$app->user->identity instanceof StaffAccount) {
            /** @var StaffAccount $account */

            $account = Yii::$app->user->identity;
            $this->assignee_ids[] = $account->profile->id;
        }

        if (!isset($this->currency_code) || !$skipIfSet) {
            $baseCurrency = Yii::$app->setting->get('finance/base_currency');

            $this->currency_code = $this->customer_id ? $this->customer->currency_code : $baseCurrency;

            if ($this->currency_code === $baseCurrency) {
                $this->currency_rate = 1;
            }
        }

        if (!isset($this->date) || !$skipIfSet) {
            $this->date = time();
        }

        if (!isset($this->status) || !$skipIfSet) {
            $this->status = self::STATUS_DRAFT;
        }

        return parent::loadDefaultValues($skipIfSet);
    }

    /**
     * @param bool $status
     *
     * @return array|string|null
     */
    public static function statuses($status = false)
    {
        $statuses = [
            self::STATUS_DRAFT => Yii::t('app', 'Draft'),
            self::STATUS_PUBLISHED => Yii::t('app', 'Published'),
            self::STATUS_CLOSED => Yii::t('app', 'Closed'),
            self::STATUS_CANCELLED => Yii::t('app', 'Cancelled'),
        ];

        if ($status !== false) {
            return isset($statuses[$status]) ? $statuses[$status] : null;
        }

        return $statuses;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'number' => Yii::t('app', 'Number'),
            'assignee_ids' => Yii::t('app', 'Assign to'),
            'customer_id' => Yii::t('app', 'Customer'),
            'currency_code' => Yii::t('app', 'Currency'),
            'status_id' => Yii::t('app', 'Status ID'),
            'sub_total' => Yii::t('app', 'Sub Total'),
            'discount' => Yii::t('app', 'Discount'),
            'tax' => Yii::t('app', 'Tax'),
            'grand_total' => Yii::t('app', 'Grand Total'),
            'total_paid' => Yii::t('app', 'Total Paid'),
            'total_due' => Yii::t('app', 'Total Due'),
            'is_published' => Yii::t('app', 'Is Published'),
            'is_paid' => Yii::t('app', 'Is Paid'),
            'is_assignee_allowed_to_add_payment' => Yii::t('app', 'Allow assignee to add payment manually'),
            'is_assignee_allowed_to_add_discount' => Yii::t('app', 'Allow assignee to add discount'),
            'is_assignee_allowed_to_update_item' => Yii::t('app', 'Allow assignee to update/add/remove item'),
            'is_assignee_allowed_to_cancel' => Yii::t('app', 'Allow assignee to cancel this invoice'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return string|null
     */
    public function getStatusText()
    {
        return self::statuses($this->status);
    }

    /**
     * @return ActiveQuery|InvoiceItemQuery
     */
    public function getItems()
    {
        if ($this->_items) {
            return $this->_items;
        }

        return $this->hasMany(InvoiceItem::class, ['invoice_id' => 'id']);
    }

    /**
     * @return ActiveQuery|InvoicePaymentQuery
     */
    public function getPayments()
    {
        return $this->hasMany(InvoicePayment::class, ['invoice_id' => 'id'])->alias('payments_of_invoice');
    }

    /**
     * @param InvoiceItem[] $items
     */
    public function setItems($items)
    {
        $this->_items = $items;
    }

    /**
     * @return ActiveQuery|CustomerQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id'])->alias('customer_of_invoice');
    }

    /**
     * @return ActiveQuery|CurrencyQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['code' => 'currency_code'])->alias('currency_of_invoice');
    }

    /**
     * @return ActiveQuery|InvoiceAssigneeQuery
     */
    public function getAssigneesRelationship()
    {
        return $this->hasMany(InvoiceAssignee::class, ['invoice_id' => 'id'])->alias('assignees_of_invoice');
    }

    /**
     * @return ActiveQuery
     */
    public function getAssignees()
    {
        return $this->hasMany(Staff::class, ['id' => 'assignee_id'])->via('assigneesRelationship');
    }

    /**
     * @return bool
     */
    public function getIsPastDue()
    {
        return time() > $this->due_date && $this->total_due > 0;
    }

    /**
     * @param null|string|int|Staff $assignor
     *
     * @return bool
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws Throwable
     */
    protected function saveAssignees($assignor = null)
    {
        if ($assignor === null && !Yii::$app->user->isGuest && Yii::$app->user->identity instanceof StaffAccount) {
            /** @var StaffAccount $account */
            $account = Yii::$app->user->identity;

            $assignor = $account->profile;
        }

        /** @var InvoiceAssignee[] $currentModels */
        $currentModels = $this->getAssigneesRelationship()->indexBy('assignee_id')->all();

        foreach ($currentModels AS $key => $model) {
            if (in_array($key, $this->assignee_ids)) {
                continue;
            }

            if (!$model->delete()) {
                return false;
            }
        }

        $addedAssignees = [];

        foreach ($this->assignee_ids AS $assigneeId) {
            if (isset($currentModels[$assigneeId])) {
                continue;
            }

            $model = new InvoiceAssignee([
                'scenario' => 'admin/invoice/add',
                'invoice_id' => $this->id,
                'assignee_id' => $assigneeId,
                'assignor_id' => $assignor->id,
            ]);

            $model->loadDefaultValues();

            if (!$model->save()) {
                return false;
            }

            $addedAssignees[] = $model;
        }

        InvoiceAssignee::sendAssignNotification($addedAssignees, $this, $assignor);

        $this->assignee_ids = [];

        return true;
    }

    /**
     * @return bool
     *
     * @throws StaleObjectException
     * @throws Throwable
     * @throws InvalidConfigException
     */
    protected function saveItems()
    {
        $ids = [];

        foreach ($this->itemModels AS $model) {
            $model->invoice_id = $this->id;
            $model->recalculateInvoice = false;

            if (!$model->save(false)) {
                return false;
            }

            $ids[] = $model->id;
        }

        $deletedModels = InvoiceItem::find()->andWhere(['NOT IN', 'id', array_filter($ids)])->andWhere(['invoice_id' => $this->id])->all();

        foreach ($deletedModels as $deletedModel) {
            $deletedModel->recalculateInvoice = false;

            if (!$deletedModel->delete()) {
                return false;
            }
        }

        $this->itemModels = [];

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
            'model_id' => $this->id
        ];

        if ($this->scenario === 'admin/add' && $insert) {
            $history['description'] = 'Adding invoice "{number}" to "{customer_name}"';
        } else {
            $history['description'] = 'Updating {customer_name}\'s invoice "{number}"';
        }

        $historyEvent = $this->scenario === 'admin/add' ? 'invoice.add' : 'invoice.update';
        $history['tag'] = $this->scenario === 'admin/add' ? 'add' : 'update';

        return Account::history()->save($historyEvent, $history);
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'number', 'customer_id', 'currency_code', 'grand_total']);
        $params['customer_name'] = $this->customer->name;

        return $params;
    }
}
