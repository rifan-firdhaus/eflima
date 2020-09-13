<?php namespace modules\account\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\StaffAccount;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class StaffAccountQuery extends AccountQuery
{
    /**
     * @inheritdoc
     *
     * @return StaffAccount[]|array
     */
    public function all($db = null)
    {
        $this->type('staff');

        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return StaffAccount|array|null
     */
    public function one($db = null)
    {
        $this->type('staff');

        return parent::one($db);
    }
}