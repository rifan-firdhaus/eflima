<?php namespace modules\core\web;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\web\Controller as BaseController;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Controller extends BaseController
{
    const EVENT_INIT = 'init';

    public function init()
    {
        $this->trigger(self::EVENT_INIT);

        parent::init();
    }
}
