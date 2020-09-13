<?php namespace modules\finance\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\finance\models\queries\InvoiceItemTaxQuery;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 *
 * @property InvoiceItem  $invoiceItem
 * @property Tax          $tax
 *
 * @property int          $id              [int(10) unsigned]
 * @property int          $tax_id          [int(11) unsigned]
 * @property int          $invoice_item_id [int(11) unsigned]
 * @property string|float $rate            [decimal(8,5)]
 * @property string|float $value           [decimal(25,10)]
 * @property string|float $real_value      [decimal(25,10)]
 */
class InvoiceItemTax extends ActiveRecord
{
    protected $_invoiceItem;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invoice_item_tax}}';
    }

    /**
     * @inheritdoc
     * @return InvoiceItemTaxQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new InvoiceItemTaxQuery(get_called_class());

        return $query->alias("invoice_item_tax");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tax_id'], 'required', 'on' => ['default', 'admin/add', 'admin/update', 'admin/temp']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'tax_id' => Yii::t('app', 'Tax ID'),
            'invoice_item_id' => Yii::t('app', 'Invoice Item ID'),
            'rate' => Yii::t('app', 'Rate'),
            'value' => Yii::t('app', 'Value'),
        ];
    }

    /**
     * @return ActiveQuery|InvoiceItem
     */
    public function getInvoiceItem()
    {
        if ($this->_invoiceItem) {
            return $this->_invoiceItem;
        }

        return $this->hasOne(InvoiceItem::class, ['id' => 'invoice_item_id']);
    }

    /**
     * @param InvoiceItem $invoiceItem
     */
    public function setInvoiceItem($invoiceItem)
    {
        $this->_invoiceItem = $invoiceItem;
    }

    /**
     * @return ActiveQuery|Tax
     */
    public function getTax()
    {
        return $this->hasOne(Tax::className(), ['id' => 'tax_id']);
    }

    /**
     * @inheritDoc
     */
    public function normalizeAttributes($save = false)
    {
        if ($save) {
            $this->rate = $this->tax->rate;
            $this->value = $this->invoiceItem->sub_total * ($this->rate / 100);
            $this->real_value = $this->value * $this->invoiceItem->invoice->currency_rate;
        }
    }

    public function fields()
    {
        $fields = parent::fields();

        if ($this->scenario === 'admin/temp') {
            $fields = $this->safeAttributes();

            $fields[] = 'id';
        }

        return $fields;
    }
}
