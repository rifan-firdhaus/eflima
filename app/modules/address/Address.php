<?php namespace modules\address;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use app\modules\account\web\admin\Application as AdminApplication;
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\address\components\Hook;
use modules\address\components\SettingObject;
use modules\core\base\Module;
use modules\core\components\SettingRenderer;
use Yii;
use yii\base\Event;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Address extends Module
{

    public function init()
    {
        parent::init();

        Hook::instance();
    }
}