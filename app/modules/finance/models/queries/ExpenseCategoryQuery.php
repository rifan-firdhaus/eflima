<?php

namespace modules\finance\models\queries;

use modules\core\db\ActiveQuery;
use modules\finance\models\ExpenseCategory;

/**
 * This is the ActiveQuery class for [[\modules\finance\models\ExpenseCategory]].
 *
 * @see ExpenseCategory
 */
class ExpenseCategoryQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return ExpenseCategory[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return ExpenseCategory|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
