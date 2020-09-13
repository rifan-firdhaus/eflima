<?php

namespace modules\finance\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use Exception;
use modules\account\Account;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\crm\models\Customer;
use modules\crm\models\queries\CustomerQuery;
use modules\finance\behaviorss\ExpenseCategoryCreationBehavior;
use modules\finance\models\queries\CurrencyQuery;
use modules\finance\models\queries\ExpenseCategoryQuery;
use modules\finance\models\queries\ExpenseQuery;
use modules\finance\models\queries\InvoiceItemQuery;
use modules\finance\models\queries\TaxQuery;
use Throwable;
use Yii;
use yii\base\Event;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as DbException;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property ExpenseCategory     $category
 * @property Customer            $customer
 * @property ExpenseTax[]        $taxes
 * @property Currency            $currency
 * @property ExpenseAttachment[] $attachments
 * @property InvoiceItem         $invoiceItem
 * @property bool                $isBilled
 * @property bool                $isInvoiceFieldUpdatable
 *
 * @property int                 $id              [int(10) unsigned]
 * @property int                 $category_id     [int(11) unsigned]
 * @property int                 $customer_id     [int(11) unsigned]
 * @property int                 $invoice_item_id [int(11) unsigned]
 * @property string              $currency_code   [char(3)]
 * @property int                 $date            [int(11) unsigned]
 * @property string              $reference
 * @property string              $name
 * @property string              $currency_rate   [decimal(25,10)]
 * @property string              $amount          [decimal(25,10)]
 * @property string              $tax             [decimal(25,10)]
 * @property string              $total           [decimal(25,10)]
 * @property string              $real_total      [decimal(25,10)]
 * @property string              $real_tax        [decimal(25,10)]
 * @property string              $real_amount     [decimal(25,10)]
 * @property string              $description
 * @property bool                $is_tax_included [tinyint(1)]
 * @property bool                $is_billable     [tinyint(1)]
 * @property int                 $created_at      [int(11) unsigned]
 * @property int                 $updated_at      [int(11) unsigned]
 * @property int                 $project_id      [int(11) unsigned]
 */
class Expense extends ActiveRecord
{
    public $tax_inputs;
    public $uploaded_attachments = [];
    public $new_category;
    protected $_recalculated = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%expense}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['name', 'date', 'category_id', 'amount'],
                'required',
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                'currency_rate',
                'required',
                'when' => function ($model) {
                    return Yii::$app->setting->get('finance/base_currency') != $model->currency_code;
                },
            ],
            [
                ['date'],
                'date',
            ],
            [
                ['is_billable', 'is_tax_included'],
                'boolean',
            ],
            [
                'category_id',
                'exist',
                'targetRelation' => 'category',
                'when' => function ($model) {
                    return empty($model->new_category);
                },
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
                ['amount'],
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
                ['description', 'reference', 'customer_id', 'tax_inputs', 'new_category'],
                'safe',
            ],
            [
                ['amount', 'currency_rate', 'currency_code', 'customer_id'],
                'validateInvoice',
            ],
        ];
    }

    /**
     * @param $attribute
     */
    public function validateInvoice($attribute)
    {
        if (
            !$this->is_billable ||
            !$this->isBilled ||
            $this->hasErrors() ||
            !$this->isAttributeChanged($attribute, false)
        ) {
            return;
        }

        if ($this->invoiceItem->invoice->status !== Invoice::STATUS_DRAFT) {
            $this->addError($attribute, Yii::t('app', 'You cant\'t change the {label}. You can change this field only if the invoice is drafted', [
                'label' => $this->getAttributeLabel($attribute),
            ]));
        }
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
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
        ];

        $behaviors['attributeTypecast'] = [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'customer_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                'category_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                'currency_rate' => AttributeTypecastBehavior::TYPE_FLOAT,

                'total' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_total' => AttributeTypecastBehavior::TYPE_FLOAT,

                'tax' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_tax' => AttributeTypecastBehavior::TYPE_FLOAT,

                'amount' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_amount' => AttributeTypecastBehavior::TYPE_FLOAT,
            ],
        ];

        $behaviors['expenseCategoryCreation'] = [
            'class' => ExpenseCategoryCreationBehavior::class,
            'attribute' => 'category_id',
            'aliasAttribute' => 'new_category',
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_id' => Yii::t('app', 'Category'),
            'customer_id' => Yii::t('app', 'Customer'),
            'currency_code' => Yii::t('app', 'Currency'),
            'date' => Yii::t('app', 'Date'),
            'reference' => Yii::t('app', 'Reference'),
            'name' => Yii::t('app', 'Name'),
            'amount' => Yii::t('app', 'Amount'),
            'uploaded_attachments' => Yii::t('app', 'Attachment'),
            'tax' => Yii::t('app', 'Tax'),
            'total' => Yii::t('app', 'Total'),
            'description' => Yii::t('app', 'Description'),
            'is_tax_included' => Yii::t('app', 'Tax Included'),
            'is_billable' => Yii::t('app', 'Billable'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ExpenseCategoryQuery|ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ExpenseCategory::class, ['id' => 'category_id'])->alias('category_of_expense');
    }

    /**
     * @return CustomerQuery|ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id'])->alias('customer_of_expense');
    }

    /**
     * @return CurrencyQuery|ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['code' => 'currency_code'])->alias('currency_of_expense');
    }

    /**
     * @return ActiveQuery|InvoiceItemQuery
     */
    public function getInvoiceItem()
    {
        return $this->hasOne(InvoiceItem::class, ['id' => 'invoice_item_id'])->alias('invoice_item_of_expense');
    }

    /**
     * @inheritdoc
     *
     * @return ExpenseQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new ExpenseQuery(get_called_class());

        return $query->alias("expense");
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        $baseCurrency = Yii::$app->setting->get('finance/base_currency');

        if (!isset($this->currency_code) || !$skipIfSet) {
            $this->currency_code = $this->customer_id ? $this->customer->currency_code : $baseCurrency;
        }

        if ((!isset($this->currency_rate) || !$skipIfSet) && $this->currency_code === $baseCurrency) {
            $this->currency_rate = 1;
        }

        return parent::loadDefaultValues($skipIfSet);
    }

    /**
     * @inheritDoc
     */
    public function normalizeAttributes($save = false)
    {
        if ($save) {
            if (Yii::$app->setting->get('finance/base_currency') === $this->currency_code) {
                $this->currency_rate = 1;
            }

            $this->tax = floatval($this->getTaxes()->sum('value'));
            $this->total = $this->amount + $this->tax;
            $this->real_total = $this->total * $this->currency_rate;
            $this->real_tax = $this->tax * $this->currency_rate;
            $this->real_amount = $this->amount * $this->currency_rate;
        } else {
            $this->tax_inputs = ArrayHelper::toArray($this->taxes);
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $isManualUpdate = in_array($this->scenario, ['admin/add', 'admin/update']);
        $realChangedAttributes = $changedAttributes;

        // Save attachments
        if ($this->uploaded_attachments) {
            if (!$this->saveAttachments()) {
                throw new DbException('Failed to save Attachment');
            }
        }

        // Save taxs
        if (isset($this->tax_inputs)) {
            if (!$this->saveTaxes()) {
                throw new DbException('Failed to save taxes');
            }

            $this->tax_inputs = null;
        }

        if (!$this->_recalculated) {
            $this->_recalculated = true;

            if (!$this->save(false)) {
                throw new DbException('Failed to recalculate expense');
            }

            $this->_recalculated = false;
        }

        // Update invoice item related to this expense when the expense total is changed
        if ($this->is_billable && $this->isBilled && $this->total) {
            if (!$this->updateInvoice()) {
                throw new DbException('Failed to update invoice item');
            }
        }

        if ($isManualUpdate && !empty($realChangedAttributes)) {
            $this->recordSavedHistory($insert);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return ActiveQuery|TaxQuery
     */
    public function getTaxes()
    {
        return $this->hasMany(ExpenseTax::class, ['expense_id' => 'id'])->alias('taxes_of_expense');
    }

    /**
     * @return ActiveQuery|ExpenseQuery
     */
    public function getAttachments()
    {
        return $this->hasMany(ExpenseAttachment::class, ['expense_id' => 'id'])->alias('attachments_of_expense');
    }

    /**
     * @return bool
     */
    public function getIsBilled()
    {
        return !empty($this->invoice_item_id);
    }

    /**
     * @return bool
     */
    public function getIsInvoiceFieldUpdatable()
    {
        return !$this->is_billable || !$this->isBilled || $this->invoiceItem->invoice->status === Invoice::STATUS_DRAFT;
    }

    /**
     * @return bool
     *
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    protected function saveTaxes()
    {
        $currentTaxIds = [];

        if (is_array($this->tax_inputs)) {
            foreach ($this->tax_inputs AS $tax) {
                if (!empty($tax['id'])) {
                    $model = ExpenseTax::find()->andWhere(['id' => $tax['id'], 'expense_id' => $this->id])->one();

                    if (!$model) {
                        throw new InvalidArgumentException('Tax doesn\'t exists');
                    }
                } else {
                    $model = new ExpenseTax([
                        'expense_id' => $this->id,
                    ]);
                }

                $model->setAttributes($tax);

                if (!$model->save()) {
                    return false;
                }

                $currentTaxIds[] = $model->id;
            }
        }

        $taxQuery = ExpenseTax::find()->andWhere(['expense_id' => $this->id]);

        if (!empty($currentTaxIds)) {
            $taxQuery->andWhere(['NOT IN', 'id', $currentTaxIds]);
        }

        $taxes = $taxQuery->all();

        foreach ($taxes AS $tax) {
            if (!$tax->delete()) {
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
            $model = new ExpenseAttachment([
                'uploaded_file' => $attachment,
                'expense_id' => $this->id,
            ]);

            if (!$model->save()) {
                return false;
            }
        }

        $this->uploaded_attachments = [];

        return true;
    }

    /**
     * @param Expense[] $expenses
     * @param Invoice   $invoice
     *
     * @return bool
     *
     * @throws DbException
     * @throws Throwable
     */
    public static function addAllToInvoice($expenses, $invoice)
    {
        $transaction = self::getDb()->beginTransaction();

        try {

            foreach ($expenses AS $expense) {
                if (!$expense->addToInvoice($invoice)) {
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
     * @return bool
     */
    protected function updateInvoice()
    {
        if (!$this->isBilled) {
            return false;
        }

        $this->invoiceItem->price = $this->real_total;
        $this->invoiceItem->amount = 1;

        return $this->invoiceItem->save(false);
    }

    /**
     * @param Invoice|int $invoice
     *
     * @return bool
     * @throws Throwable
     */
    public function addToInvoice($invoice)
    {
        $transaction = self::getDb()->beginTransaction();

        if (!$invoice instanceof Invoice) {
            $invoice = Invoice::find()->andWhere(['id' => $invoice])->one();
        }

        try {
            $invoiceItem = new InvoiceItem([
                'scenario' => 'admin/add',
                'invoice_id' => $invoice->id,
                'type' => 'expense',
                'params' => [
                    'expense_id' => $this->id,
                ],
                'name' => $this->name,
                'price' => $this->real_total,
                'amount' => 1,
            ]);

            $invoiceItem->loadDefaultValues();

            foreach ($this->taxes AS $tax) {
                $invoiceItem->tax_inputs[] = [
                    'tax_id' => $tax->tax_id,
                ];
            }

            if (!$invoiceItem->save()) {
                $transaction->rollBack();

                return false;
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
     * @param InvoiceItem|string|int $invoiceItem
     *
     * @return bool
     *
     * @throws DbException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function attachToInvoiceItem($invoiceItem)
    {
        if (!$invoiceItem instanceof InvoiceItem) {
            $invoiceItem = InvoiceItem::find()->andWhere(['id' => $invoiceItem])->one();

            if (!$invoiceItem) {
                throw new DbException("Invoice item doesn't exists");
            }
        }

        $this->invoice_item_id = $invoiceItem->id;

        if (!$this->save(false)) {
            return false;
        }

        $this->recordBilledHistory();

        return true;
    }

    /**
     * @param InvoiceItem $invoiceItem
     *
     * @return bool
     */
    public function detachFromInvoiceItem($invoiceItem)
    {
        $this->invoice_item_id = null;

        if (!$this->save(false)) {
            return false;
        }

        $this->recordUnbilledHistory($invoiceItem);

        return true;
    }

    /**
     * @param Event $event
     *
     * @throws InvalidConfigException
     */
    public static function eventInvoiceItemDeleted($event)
    {
        /** @var InvoiceItem $model */
        $model = $event->sender;

        if ($model->type === 'expense') {
            $expense = self::find()->andWhere(['invoice_item_id' => $model->id])->one();

            if ($expense) {
                $expense->detachFromInvoiceItem($model);
            }
        }
    }

    /**
     * @param Event $event
     *
     * @throws InvalidConfigException
     *
     * @throws DbException
     * @throws Throwable
     */
    public static function eventInvoiceItemSaved($event)
    {
        /** @var InvoiceItem $model */
        $model = $event->sender;

        if ($model->type === 'expense') {
            $params = is_array($model->params) ? $model->params : Json::decode($model->params);

            $expense = self::find()->andWhere(['id' => $params['expense_id']])->one();

            if ($expense && empty($expense->invoice_item_id)) {
                $expense->attachToInvoiceItem($model);
            }
        }
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'name', 'customer_id']);

        $params['customer_name'] = $this->customer->name;

        return $params;
    }

    /**
     * @return bool
     *
     * @throws DbException
     * @throws Throwable
     */
    public function recordBilledHistory()
    {
        $history = [
            'params' => $this->getHistoryParams(),
            'model' => self::class,
            'model_id' => $this->id,
            'description' => 'Billed expense "{name}" in invoice "{invoice_number}"',
        ];

        $history['params']['invoice_id'] = $this->invoiceItem->invoice_id;
        $history['params']['invoice_number'] = $this->invoiceItem->invoice->number;

        return Account::history()->save('expense.billed', $history);
    }

    /**
     * @param InvoiceItem $invoiceItem
     *
     * @return bool
     *
     * @throws DbException
     * @throws Throwable
     */
    public function recordUnbilledHistory($invoiceItem)
    {
        $history = [
            'params' => $this->getHistoryParams(),
            'model' => self::class,
            'model_id' => $this->id,
            'description' => 'Unbilled expense "{name}" from invoice "{invoice_number}"',
        ];

        $history['params']['invoice_id'] = $invoiceItem->invoice_id;
        $history['params']['invoice_number'] = $invoiceItem->invoice->number;

        return Account::history()->save('expense.unbilled', $history);
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
            $history['description'] = 'Adding expense "{name}"';
        } else {
            $history['description'] = 'Updating expense "{name}"';
        }

        $historyEvent = $this->scenario === 'admin/add' ? 'expense.add' : 'expense.update';
        $history['tag'] = $this->scenario === 'admin/add' ? 'add' : 'update';

        return Account::history()->save($historyEvent, $history);
    }
}
