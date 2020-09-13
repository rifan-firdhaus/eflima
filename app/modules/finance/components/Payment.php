<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use http\Exception\InvalidArgumentException;
use modules\finance\models\InvoicePayment;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property string $label
 */
abstract class Payment extends Component
{
    const STATUS_WAITING = 'W';
    const STATUS_REJECTED = 'R';
    const STATUS_CANCELLED = 'C';
    const STATUS_REFUNDED = 'B';
    const STATUS_ACCEPTED = 'A';

    public $id;
    public $private = false;

    protected static $payments = [];

    /** @var array|Payment[] */
    protected static $instances = [];


    /**
     * @return string
     */
    abstract public function getLabel();

    /**
     * @param InvoicePayment $model
     *
     * @return boolean
     */
    abstract public function pay($model);

    /**
     * @param InvoicePayment $model
     *
     * @return boolean
     */
    public function manualPay($model)
    {
        $model->status = self::STATUS_ACCEPTED;
        $model->accepted_at = time();

        return $model->save();
    }

    /**
     * @param InvoicePayment $model
     *
     * @return mixed
     */
    public function paymentNotification($model)
    {

    }

    /**
     * @param bool|string $status
     *
     * @return array|string|null
     */
    public static function statuses($status = false)
    {
        $statuses = [
            self::STATUS_WAITING => Yii::t('app', 'Waiting'),
            self::STATUS_CANCELLED => Yii::t('app', 'Cancelled'),
            self::STATUS_ACCEPTED => Yii::t('app', 'Accepted'),
            self::STATUS_REFUNDED => Yii::t('app', 'Refunded'),
            self::STATUS_REJECTED => Yii::t('app', 'Rejected'),
        ];

        if ($status !== false) {
            return isset($statuses[$status]) ? $statuses[$status] : null;
        }

        return $statuses;
    }

    /**
     * @param                $id
     * @param string|Payment $class
     */
    public static function register($id, $class)
    {
        self::$payments[$id] = $class;
    }

    /**
     * @param $id
     *
     * @return Payment
     * @throws InvalidConfigException
     */
    public static function get($id)
    {
        if (!isset(self::$payments[$id])) {
            throw new InvalidArgumentException("Payment with id: {$id} doesn't exists");
        }

        if (!isset(self::$instances[$id])) {
            $options = [];
            $class = self::$payments[$id];

            if (is_array($class)) {
                $options = self::$payments[$id];
                $class = ArrayHelper::remove($options, 'class');
            }

            self::$instances[$id] = Yii::createObject($class, $options);
        }

        return self::$instances[$id];
    }

    /**
     * @return array|Payment[]
     * @throws InvalidConfigException
     */
    public static function all()
    {
        $payments = [];

        foreach (self::$payments AS $id => $option) {
            $payments[$id] = self::get($id);
        }

        return $payments;
    }

    public static function map(){
        $paymentMethods = [];

        foreach (Payment::all() AS $id => $payment) {
            $paymentMethods[$id] = $payment->getLabel();
        }

        return $paymentMethods;
    }
}