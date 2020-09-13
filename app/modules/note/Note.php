<?php namespace modules\note;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use app\modules\account\web\admin\Application as AdminApplication;
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\core\base\Module;
use modules\note\components\Hook;
use Yii;
use yii\base\Event;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Note extends Module
{
    public function init()
    {
        parent::init();

        Hook::instance();
    }
}