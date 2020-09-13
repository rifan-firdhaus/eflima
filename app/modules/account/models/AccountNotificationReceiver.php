<?php namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\queries\AccountNotificationQuery;
use modules\account\models\queries\AccountNotificationReceiverQuery;
use modules\account\models\queries\AccountQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Account|null        $account
 * @property AccountNotification $notification
 *
 * @property int                 $id              [int(10) unsigned]
 * @property string              $notification_id [char(16)]
 * @property string              $account_type    [varchar(16)]
 * @property int                 $account_id      [int(11) unsigned]
 * @property bool                $is_read         [tinyint(1)]
 * @property bool                $is_seen         [tinyint(1)]
 */
class AccountNotificationReceiver extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_notification_receiver}}';
    }

    /**
     * @inheritdoc
     *
     * @return AccountNotificationReceiverQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new AccountNotificationReceiverQuery(get_called_class());

        return $query->alias("account_notification_receiver");
    }


    /**
     * @return ActiveQuery|AccountQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'account_id'])->alias('account_of_notification_receiver');
    }

    /**
     * @return ActiveQuery|AccountNotificationQuery
     */
    public function getNotification()
    {
        return $this->hasOne(AccountNotification::class, ['id' => 'notification_id'])->alias('notification_of_receiver');
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'notification_id' => Yii::t('app', 'Notification ID'),
            'account_id' => Yii::t('app', 'Account ID'),
            'is_read' => Yii::t('app', 'Is Read'),
            'is_seen' => Yii::t('app', 'Is Seen'),
        ];
    }
}
