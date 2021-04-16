<?php namespace modules\finance\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveQuery;
use modules\finance\models\InvoiceItemTax;

/**
 * @author Rifan Firdhaus Widigdo
 * <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\finance\models\InvoiceItemTax]].
 *
 * @see    \modules\finance\models\InvoiceItemTax
 */
class InvoiceItemTaxQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return InvoiceItemTax[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return InvoiceItemTax|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
