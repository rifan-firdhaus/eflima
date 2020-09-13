<?php namespace modules\crm\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\queries\AccountQuery;
use modules\crm\models\CustomerContactAccount;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CustomerContactAccountQuery extends AccountQuery
{
    /**
     * @inheritdoc
     *
     * @return CustomerContactAccount[]|array
     */
    public function all($db = null)
    {
        $this->type('customer');

        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return CustomerContactAccount|array|null
     */
    public function one($db = null)
    {
        $this->type('customer');

        return parent::one($db);
    }
}