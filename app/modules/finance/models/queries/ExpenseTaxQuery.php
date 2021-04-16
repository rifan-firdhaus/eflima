<?php

namespace modules\finance\models\queries;

use modules\core\db\ActiveQuery;
use modules\finance\models\ExpenseTax;

/**
 * This is the ActiveQuery class for [[\modules\finance\models\ExpenseTax]].
 *
 * @see \modules\finance\models\ExpenseTax
 */
class ExpenseTaxQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return ExpenseTax[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return ExpenseTax|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
