<?php namespace modules\ui;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\ui\components\Hook;
use modules\core\base\Module;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class UI extends Module
{
    public function init()
    {
        parent::init();

        Hook::instance();
    }
}