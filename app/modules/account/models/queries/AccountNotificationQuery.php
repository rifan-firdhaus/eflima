<?php namespace modules\account\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\Account;
use modules\account\models\AccountNotification;
use modules\account\models\AccountNotificationReceiver;
use modules\core\db\ActiveQuery;
use yii\base\InvalidConfigException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * This is the ActiveQuery class for [[\modules\account\models\AccountNotification]].
 *
 * @see    AccountNotification
 */
class AccountNotificationQuery extends ActiveQuery
{
    public $receiverJoined = false;

    /**
     * @inheritdoc
     *
     * @return AccountNotification[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return AccountNotification|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $account
     *
     * @return AccountNotificationQuery
     *
     * @throws InvalidConfigException
     */
    public function notSeen($account)
    {
        return $this->seen($account, false);
    }

    /**
     * @param      $account
     * @param bool $isSeen
     *
     * @return AccountNotificationQuery
     *
     * @throws InvalidConfigException
     */
    public function seen($account, $isSeen = true)
    {
        $name = AccountNotificationReceiver::tableName();

        return $this->to($account)->andWhere(["{$name}.is_seen" => $isSeen]);
    }

    /**
     * @param Account|int|string $account
     *
     * @return AccountNotificationQuery
     *
     * @throws InvalidConfigException
     */
    public function to($account)
    {
        if ($this->receiverJoined) {
            return $this;
        }

        if (!($account instanceof Account)) {
            $account = Account::find()->andWhere(['id' => $account])->one();
        }

        $this->receiverJoined = $account;

        $name = AccountNotificationReceiver::tableName();

        return $this->join('LEFT JOIN', AccountNotificationReceiver::tableName(), [
            'AND',
            "{$name}.notification_id = {$this->getAlias()}.id",
            [
                'OR',
                ["{$name}.account_type" => $account->type],
                ["{$name}.account_id" => $account->id],
            ],
        ])->andWhere([
            'AND',
            ['IS NOT', "{$this->getAlias()}.id", null],
            ['IS NOT', "{$name}.id", null],
        ]);
    }

    /**
     * @param $account
     *
     * @return AccountNotificationQuery
     *
     * @throws InvalidConfigException
     */
    public function notRead($account)
    {
        return $this->read($account, false);
    }

    /**
     * @param      $account
     * @param bool $isRead
     *
     * @return AccountNotificationQuery
     *
     * @throws InvalidConfigException
     */
    public function read($account, $isRead = true)
    {
        $name = AccountNotificationReceiver::tableName();

        return $this->to($account)->andWhere(["{$name}.is_read" => $isRead]);
    }
}
