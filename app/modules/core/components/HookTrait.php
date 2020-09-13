<?php namespace modules\core\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait HookTrait
{
    protected static $_instance;

    public static function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
    }
}