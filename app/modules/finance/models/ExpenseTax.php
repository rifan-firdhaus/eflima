<?php

namespace modules\finance\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\finance\models\queries\ExpenseQuery;
use modules\finance\models\queries\ExpenseTaxQuery;
use modules\finance\models\queries\TaxQuery;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Expense $expense
 * @property Tax     $tax
 *
 * @property int     $id         [int(10) unsigned]
 * @property int     $tax_id     [int(11) unsigned]
 * @property int     $expense_id [int(11) unsigned]
 * @property string  $rate       [decimal(8,5)]
 * @property string  $value      [decimal(25,10)]
 * @property string  $real_value [decimal(25,10)]
 */
class ExpenseTax extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%expense_tax}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tax_id'], 'required'],
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
            'expense_id' => Yii::t('app', 'Expense ID'),
            'rate' => Yii::t('app', 'Rate'),
            'value' => Yii::t('app', 'Value'),
        ];
    }

    /**
     * @return ExpenseQuery|ActiveQuery
     */
    public function getExpense()
    {
        return $this->hasOne(Expense::class, ['id' => 'expense_id']);
    }

    /**
     * @return TaxQuery|ActiveQuery
     */
    public function getTax()
    {
        return $this->hasOne(Tax::class, ['id' => 'tax_id']);
    }

    /**
     * @inheritdoc
     *
     * @return ExpenseTaxQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new ExpenseTaxQuery(get_called_class());

        return $query->alias("expense_tax");
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->rate = $this->tax->rate;
        $this->value = $this->expense->amount * ($this->rate / 100);

        return true;
    }
}
