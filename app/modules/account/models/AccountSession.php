<?php

namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\queries\AccountSessionQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use Yii;
use yii\db\Exception;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Account $account
 *
 * @property int     $id               [int(10) unsigned]
 * @property int     $account_id       [int(11) unsigned]
 * @property string  $session_id
 * @property string  $user_agent
 * @property string  $ip
 * @property int     $logged_in_at     [int(11) unsigned]
 * @property int     $logged_out_at    [int(11) unsigned]
 * @property int     $last_activity_at [int(11) unsigned]
 */
class AccountSession extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_session}}';
    }

    /**
     * @inheritdoc
     *
     * @return AccountSessionQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new AccountSessionQuery(get_called_class());

        return $query->alias("account_session");
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'account_id' => Yii::t('app', 'Account'),
            'session_id' => Yii::t('app', 'Session'),
            'user_agent' => Yii::t('app', 'User Agent'),
            'ip' => Yii::t('app', 'IP'),
            'logged_in_at' => Yii::t('app', 'Logged In At'),
            'logged_out_at' => Yii::t('app', 'Logged Out At'),
            'last_activity_at' => Yii::t('app', 'Last Activity At'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'account_id'])->alias('account_of_session');
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->account->last_activity_at = $this->last_activity_at;

        if (!$this->account->save(false)) {
            throw new Exception('Failed to save last activity');
        }
    }
}
