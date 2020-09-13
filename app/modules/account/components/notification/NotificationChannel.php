<?php namespace modules\account\components\notification;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\Account;
use yii\base\BaseObject;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property array|Account[] $toAccounts
 * @property Notification    $notification
 */
abstract class NotificationChannel extends BaseObject
{
    public $title;
    public $body;
    public $titleParams;
    public $bodyParams;
    public $data;
    public $to;
    public $toAccountType;
    /** @var Notification */
    protected $_notification;
    protected $_toAccounts;

    /**
     * @param Notification $notification
     * @param array        $config
     */
    public function __construct($notification, $config = [])
    {
        parent::__construct($config);

        $this->_notification = $notification;
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->_notification;
    }

    /**
     * @return bool
     */
    abstract public function send();

    /**
     * @return array|Account[]
     */
    public function getToAccounts()
    {
        return $this->notification->toAccounts;
    }
}