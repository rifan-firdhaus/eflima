<?php namespace modules\ui\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\Setting;
use Pusher\Pusher as RealPusher;
use Pusher\PusherException;
use Yii;
use yii\base\InvalidConfigException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Pusher
{
    protected $_appId;
    protected $_key;
    protected $_secret;
    protected $_cluster;
    protected $useTls = false;

    /** @var RealPusher */
    protected $instance;

    /** @var Pusher */
    protected static $singleton;

    /**
     * Pusher constructor.
     *
     * @throws InvalidConfigException
     */
    protected function __construct()
    {
        /** @var Setting $setting */
        $setting = Yii::$app->setting;

        $this->_appId = $setting->get('pusher/app_id');
        $this->_key = $setting->get('pusher/app_key');
        $this->_secret = $setting->get('pusher/app_secret');
        $this->_cluster = $setting->get('pusher/cluster');

        return $this;
    }

    /**
     * @return Pusher
     *
     * @throws InvalidConfigException
     */
    public static function get()
    {
        if (!isset(self::$singleton)) {
            self::$singleton = new Pusher();
        }

        return self::$singleton;
    }

    /**
     * @return RealPusher
     *
     * @throws PusherException
     */
    public function instance()
    {
        if (!isset($this->instance)) {
            $this->instance = new RealPusher($this->_key, $this->_secret, $this->_appId, [
                'useTls' => $this->useTls,
                'cluster' => $this->_cluster,
            ]);
        }

        return $this->instance;
    }

    /**
     * @param mixed $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (in_array($name, ['key', 'secret', 'appId', 'cluster', 'useTls'])) {
            $propertyName = "_{$name}";

            return $this->{$propertyName};
        }
    }
}