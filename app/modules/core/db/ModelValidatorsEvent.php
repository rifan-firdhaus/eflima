<?php namespace modules\core\db;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use ArrayObject;
use yii\base\Event;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ModelValidatorsEvent extends Event
{
    /** @var ArrayObject */
    public $validators;
}