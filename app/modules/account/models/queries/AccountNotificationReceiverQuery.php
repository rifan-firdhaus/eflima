<?php namespace modules\account\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\AccountNotificationReceiver;
use modules\core\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\account\models\AccountNotificationReceiver]].
 *
 * @see    AccountNotificationReceiver
 */
class AccountNotificationReceiverQuery extends ActiveQuery
{

    /**
     * @inheritdoc
     *
     * @return AccountNotificationReceiver[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return AccountNotificationReceiver|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
