<?php namespace modules\finance\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\helpers\Common;
use modules\crm\models\Customer;
use modules\finance\components\Payment;
use modules\finance\models\queries\InvoicePaymentQuery;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception as DbException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Invoice $invoice
 * @property Payment $method
 *
 * @property int     $id          [int(10) unsigned]
 * @property string  $number
 * @property int     $invoice_id  [int(11) unsigned]
 * @property string  $method_id   [varchar(5)]
 * @property string  $amount      [decimal(25,10)]
 * @property string  $real_amount [decimal(25,10)]
 * @property string  $status      [char(1)]
 * @property string  $data
 * @property string  $note
 * @property bool    $is_manual   [tinyint(1)]
 * @property int     $accepted_at [int(11) unsigned]
 * @property int     $at          [int(11) unsigned]
 */
class InvoicePayment extends ActiveRecord
{
    public $recalculateInvoice = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invoice_payment}}';
    }

    /**
     * @inheritdoc
     *
     * @return InvoicePaymentQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new InvoicePaymentQuery(get_called_class());

        return $query->alias("invoice_payment");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'invoice_id',
                'exist',
                'targetRelation' => 'invoice',
            ],
            [
                ['amount', 'method_id', 'at', 'invoice_id'],
                'required',
                'on' => ['admin/add'],
            ],
            [
                'amount',
                'validateAmount',
            ],
            [
                'method_id',
                'in',
                'range' => array_keys(Payment::all()),
            ],
            [
                'at',
                'datetime',
            ],
            [
                ['note'],
                'string',
            ],
        ];
    }

    /**
     * @param string $attribute
     *
     * @throws InvalidConfigException
     */
    public function validateAmount($attribute)
    {
        if ($this->hasErrors($attribute) || empty($this->invoice_id)) {
            return;
        }

        if ($this->invoice->total_due < $this->amount) {
            $this->addError($attribute, Yii::t('app', 'This invoice only has {due} due left', [
                'due' => Yii::$app->formatter->asCurrency($this->invoice->total_due, $this->invoice->currency_code),
            ]));
        }
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function generateId()
    {
        $number = Common::logicalRandom();

        if (self::find()->andWhere(['number' => $number])->exists()) {
            return self::generateId();
        }

        return $number;
    }

    /**
     * @inheritdoc
     */
    public function normalizeAttributes($save = false)
    {
        if ($save) {
            if ($this->isNewRecord) {
                $this->number = self::generateId();

                if (empty($this->at)) {
                    $this->at = time();
                }
            }

            $this->real_amount = $this->amount * $this->invoice->currency_rate;

            $this->typecastAttributes();
        }
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        if (!isset($this->status) || !$skipIfSet) {
            $this->status = Payment::STATUS_WAITING;
        }

        if ($this->invoice_id && !isset($this->amount) || !$skipIfSet) {
            $this->amount = $this->invoice->total_due;
        }

        if (!isset($this->at) || !$skipIfSet) {
            $this->at = time();
        }

        return parent::loadDefaultValues($skipIfSet);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->scenario === 'admin/add' && $insert) {
            if (!$this->manualPay()) {
                throw new DbException('Failed to do payment');
            }
        }

        if (!$this->recalculateInvoice()) {
            throw new DbException('Failed to recalculate invoice');
        }

        if (
            in_array($this->scenario, ['admin/add', 'admin/update']) &&
            !empty($changedAttributes)
        ) {
            $this->recordSavedHistory($insert);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return bool
     */
    public function manualPay()
    {
        return $this->method->manualPay($this);
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        return [
            'admin/add' => self::OP_ALL,
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['attributeTypecast'] = [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'at' => AttributeTypecastBehavior::TYPE_INTEGER,
                'accepted_at' => AttributeTypecastBehavior::TYPE_INTEGER,
                'invoice_id' => AttributeTypecastBehavior::TYPE_INTEGER,

                'amount' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_amount' => AttributeTypecastBehavior::TYPE_FLOAT,
            ],
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
            'invoice_id' => Yii::t('app', 'Invoice'),
            'method_id' => Yii::t('app', 'Method'),
            'amount' => Yii::t('app', 'Amount'),
            'status' => Yii::t('app', 'Status'),
            'at' => Yii::t('app', 'Date'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id'])->alias('invoice_of_payment');
    }

    /**
     * @return Payment
     * @throws InvalidConfigException
     */
    public function getMethod()
    {
        return Payment::get($this->method_id);
    }

    /**
     * @return bool
     */
    public function recalculateInvoice()
    {
        if (!$this->recalculateInvoice) {
            return true;
        }

        return $this->invoice->save(false);
    }

    /**
     * @param bool $insert
     *
     * @return bool|void
     * @throws DbException
     * @throws Throwable
     */
    public function recordSavedHistory($insert = false)
    {
        if ($this->scenario !== 'admin/add' || !$insert) {
            return;
        }

        $history = [
            'params' => $this->getHistoryParams(),
            'model' => Invoice::class,
            'model_id' => $this->invoice_id
        ];
        $history['description'] = 'Record payment {amount} for invoice "{invoice_number}" ';

        $historyEvent = 'invoice.pay';
        $history['tag'] = 'add';

        return Account::history()->save($historyEvent, $history);
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'number', 'invoice_id', 'amount']);
        $params['currency_code'] = $this->invoice->currency_code;
        $params['customer_name'] = $this->invoice->customer->name;
        $params['invoice_number'] = $this->invoice->number;

        return $params;
    }
}
