<?php

namespace modules\crm\models\queries;

use modules\core\db\ActiveQuery;
use modules\crm\models\Customer;

/**
 * This is the ActiveQuery class for [[\modules\crm\models\Customer]].
 *
 * @see \modules\crm\models\Customer
 */
class CustomerQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return Customer[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return Customer|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
