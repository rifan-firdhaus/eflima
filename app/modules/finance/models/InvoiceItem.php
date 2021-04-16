<?php namespace modules\finance\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use Exception;
use modules\account\Account;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\finance\models\queries\InvoiceItemQuery;
use modules\finance\models\queries\ProductQuery;
use modules\finance\models\queries\TaxQuery;
use Throwable;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as DbException;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Invoice          $invoice
 * @property InvoiceItemTax[] $taxes
 * @property-read Product     $product
 * @property-read array       $historyParams
 *
 * @property int              $id               [int(10) unsigned]
 * @property int              $invoice_id       [int(11) unsigned]
 * @property int              $product_id       [int(11) unsigned]
 * @property string           $name
 * @property string           $picture
 * @property string           $type             [varchar(64)]
 * @property string           $price            [decimal(25,10)]
 * @property string           $real_price       [decimal(25,10)]
 * @property string           $amount           [decimal(25,10)]
 * @property string           $tax              [decimal(25,10)]
 * @property string           $real_tax         [decimal(25,10)]
 * @property string           $sub_total        [decimal(25,10)]
 * @property string           $real_sub_total   [decimal(25,10)]
 * @property string           $grand_total      [decimal(25,10)]
 * @property string           $real_grand_total [decimal(25,10)]
 * @property string           $params
 * @property int              $order            [smallint(5) unsigned]
 * @property string           $terms
 * @property int              $creator_id       [int(11) unsigned]
 * @property int              $created_at       [int(11) unsigned]
 * @property int              $updater_id       [int(11) unsigned]
 * @property int              $updated_at       [int(11) unsigned]
 */
class InvoiceItem extends ActiveRecord
{
    public $tax_inputs;

    protected $_invoice;
    protected $_taxes;
    protected $_recalculated = false;

    public $recalculateInvoice = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invoice_item}}';
    }

    /**
     * @inheritdoc
     * @return InvoiceItemQuery the active quer
     * @property int $product_id [int(11) unsigned]
     * y used by this AR class.
     */
    public static function find()
    {
        $query = new InvoiceItemQuery(get_called_class());

        return $query->alias("invoice_item");
    }

    /**
     * @param $invoiceId
     * @param $sort
     *
     * @return bool
     * @throws DbException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public static function sort($invoiceId, $sort)
    {
        $models = self::find()->andWhere(['invoice_id' => $invoiceId, 'id' => $sort])->indexBy('id')->all();

        $transaction = self::getDb()->beginTransaction();

        try {
            foreach ($sort AS $order => $invoiceItemId) {
                if (!isset($models[$invoiceItemId])) {
                    continue;
                }

                $model = $models[$invoiceItemId];

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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'id',
                'safe',
                'on' => ['admin/temp/add', 'admin/temp/update'],
            ],
            [
                ['name', 'price', 'amount', 'type'],
                'required',
                'on' => ['admin/add', 'admin/temp/add', 'admin/temp/update', 'admin/update'],
            ],
            [
                ['price', 'amount'],
                'double',
                'min' => 0,
            ],
            [
                'product_id',
                'exist',
                'targetRelation' => 'product',
            ],
            [
                'product_id',
                'required',
                'when' => function ($model) {
                    return $model->type === 'product';
                },
            ],
            [
                'order',
                'integer'
            ],
            [
                ['tax_inputs', 'params'],
                'safe',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        return [
            'default' => self::OP_ALL,
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

        $behaviors['blamable'] = [
            'class' => BlameableBehavior::class,
            'createdByAttribute' => 'creator_id',
            'updatedByAttribute' => 'updater_id',
        ];

        $behaviors['attributeTypecast'] = [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'invoice_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                'product_id' => AttributeTypecastBehavior::TYPE_INTEGER,

                'amount' => AttributeTypecastBehavior::TYPE_FLOAT,

                'sub_total' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_sub_total' => AttributeTypecastBehavior::TYPE_FLOAT,

                'tax' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_tax' => AttributeTypecastBehavior::TYPE_FLOAT,

                'price' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_price' => AttributeTypecastBehavior::TYPE_FLOAT,

                'grand_total' => AttributeTypecastBehavior::TYPE_FLOAT,
                'real_grand_total' => AttributeTypecastBehavior::TYPE_FLOAT,
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function normalizeAttributes($save = false)
    {
        if ($save) {
            if (!in_array($this->scenario, ['admin/temp/add', 'admin/temp/update'])) {
                $this->tax = floatval($this->getTaxes()->sum('value'));
            }

            $this->sub_total = $this->price * $this->amount;
            $this->grand_total = $this->sub_total + $this->tax;
            $this->real_sub_total = $this->sub_total * $this->invoice->currency_rate;
            $this->real_price = $this->price * $this->invoice->currency_rate;
            $this->real_tax = $this->tax * $this->invoice->currency_rate;
            $this->real_grand_total = $this->grand_total * $this->invoice->currency_rate;

            if (is_array($this->params) && !empty($this->params)) {
                $this->params = Json::encode($this->params);
            }

            $this->typecastAttributes();
        } else {
            $this->tax_inputs = ArrayHelper::toArray($this->taxes);

            if (is_string($this->params)) {
                $this->params = Json::decode($this->params);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Save taxs
        if (isset($this->tax_inputs)) {
            if (!$this->saveTaxes()) {
                throw new DbException('Failed to save taxes');
            }

            $this->tax_inputs = null;
        }

        if (!$this->_recalculated) {
            $this->_recalculated = true;

            if (
                in_array($this->scenario, ['admin/add', 'admin/update']) &&
                !empty($changedAttributes)
            ) {
                $this->recordSavedHistory($insert);
            }

            if (!$this->save(false)) {
                throw new DbException('Failed to recalculate item');
            }

            $this->_recalculated = false;
        }

        if (!$this->recalculateInvoice()) {
            throw new DbException('Failed to recalculate invoice');
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'invoice_id' => Yii::t('app', 'Invoice'),
            'name' => Yii::t('app', 'Name'),
            'picture' => Yii::t('app', 'Picture'),
            'type' => Yii::t('app', 'Type'),
            'price' => Yii::t('app', 'Price'),
            'amount' => Yii::t('app', 'Amount'),
            'total' => Yii::t('app', 'Total'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery|ProductQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id'])->alias('product_of_invoice');
    }

    /**
     * @return ActiveQuery
     */
    public function getInvoice()
    {
        if ($this->_invoice) {
            return $this->_invoice;
        }

        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }

    /**
     * @param Invoice $invoice
     */
    public function setInvoice($invoice)
    {
        $this->_invoice = $invoice;
    }

    /**
     * @return ActiveQuery|TaxQuery
     */
    public function getTaxes()
    {
        if ($this->_taxes) {
            return $this->_taxes;
        }

        return $this->hasMany(InvoiceItemTax::class, ['invoice_item_id' => 'id']);
    }

    /**
     * @param InvoiceItemTax[] $taxes
     */
    public function setTaxes($taxes)
    {
        $this->_taxes = $taxes;
    }

    /**
     * @inheritDoc
     */
    public function fields()
    {
        $fields = parent::fields();

        if (in_array($this->scenario, ['admin/temp/add', 'admin/temp/update'])) {
            $fields = $this->safeAttributes();
            $fields[] = 'grand_total';
            $fields[] = 'sub_total';
            $fields[] = 'params';
            $fields[] = 'id';
        }

        return $fields;
    }

    /**
     * @inheritDoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        if (!isset($this->type) || !$skipIfSet) {
            $this->type = 'raw';
        }

        return parent::loadDefaultValues($skipIfSet);
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
                    $model = InvoiceItemTax::find()->andWhere(['id' => $tax['id'], 'invoice_item_id' => $this->id])->one();
                    if (!$model) {
                        throw new InvalidArgumentException('Tax doesn\'t exists');
                    }
                } else {
                    $model = new InvoiceItemTax([
                        'invoice_item_id' => $this->id,
                    ]);
                }

                $model->setAttributes($tax);

                if (!$model->save()) {
                    return false;
                }

                $currentTaxIds[] = $model->id;
            }
        }

        $taxQuery = InvoiceItemTax::find()->andWhere(['invoice_item_id' => $this->id]);

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
     * @inheritDoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        if (!$this->recalculateInvoice()) {
            throw new DbException('Failed to recalculate invoice');
        }

        $this->recordDeleteHistory();
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
     * @return bool
     *
     * @throws DbException
     * @throws Throwable
     */
    public function recordDeleteHistory()
    {
        return Account::history()->save('invoice_item.delete', [
            'params' => $this->getHistoryParams(),
            'model' => Invoice::class,
            'model_id' => $this->invoice_id,
            'tag' => 'delete',
            'description' => 'Deleting item "{name}" of invoice "{invoice_number}"',
        ]);
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
        $history = [
            'params' => $this->getHistoryParams(),
            'model' => Invoice::class,
            'model_id' => $this->invoice_id,
        ];

        if ($this->scenario === 'admin/add' && $insert) {
            $history['description'] = 'Adding item "{name}" to invoice "{invoice_number}"';
        } else {
            $history['description'] = 'Updating item "{name}" of invoice "{invoice_number}"';
        }

        $historyEvent = $this->scenario === 'admin/add' ? 'invoice_item.add' : 'invoice_item.update';
        $history['tag'] = $this->scenario === 'admin/add' ? 'add' : 'update';

        return Account::history()->save($historyEvent, $history);
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'invoice_id', 'name', 'amount', 'price', 'sub_total', 'grand_total']);
        $params['customer_name'] = $this->invoice->customer->name;
        $params['invoice_number'] = $this->invoice->number;

        return $params;
    }
}
