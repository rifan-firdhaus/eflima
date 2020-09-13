<?php

namespace modules\crm\models\queries;

use modules\core\db\ActiveQuery;
use modules\crm\models\CustomerContact;

/**
 * This is the ActiveQuery class for [[\modules\crm\models\CustomerContact]].
 *
 * @see \modules\crm\models\CustomerContact
 */
class CustomerContactQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return CustomerContact[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return CustomerContact|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
