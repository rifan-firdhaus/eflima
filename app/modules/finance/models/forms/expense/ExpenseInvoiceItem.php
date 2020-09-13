<?php namespace modules\finance\models\forms\expense;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\InvoiceItem;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ExpenseInvoiceItem extends InvoiceItem
{
    public function rules()
    {
        return [
            [
                'id',
                'safe',
                'on' => ['admin/temp/add', 'admin/temp/update'],
            ],
            ['name', 'required', 'on' => ['admin/temp/update', 'admin/update']],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();

        if (in_array($this->scenario, ['admin/temp/add', 'admin/temp/update'])) {
            $fields[] = 'amount';
            $fields[] = 'price';
            $fields[] = 'type';
            $fields[] = 'tax_inputs';
        }

        return $fields;
    }
}