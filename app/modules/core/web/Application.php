<?php namespace app\modules\core\web;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\Setting;
use yii\base\InvalidConfigException;
use yii\web\Application as BaseApplication;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property bool  $isAdmin
 * @property mixed $setting
 */
class Application extends BaseApplication
{
    public $type = 'web';

    /**
     * @return Setting|object
     * @throws InvalidConfigException
     */
    public function getSetting()
    {
        return $this->get('setting');
    }
}