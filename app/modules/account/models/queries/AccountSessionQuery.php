<?php namespace modules\account\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\AccountSession;
use modules\core\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\account\models\AccountSession]].
 *
 * @see    AccountSession
 */
class AccountSessionQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return AccountSession[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return AccountSession|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
