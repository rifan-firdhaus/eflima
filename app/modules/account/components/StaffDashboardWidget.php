<?php namespace modules\account\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\base\Component;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
abstract class StaffDashboardWidget extends Component
{
    const SIZE_1 = '1';
    const SIZE_2 = '2';
    const SIZE_3 = '3';
    const SIZE_4 = '4';
    const SIZE_5 = '5';
    const SIZE_6 = '6';
    const SIZE_7 = '7';
    const SIZE_8 = '8';
    const SIZE_9 = '9';
    const SIZE_10 = '10';
    const SIZE_11 = '11';
    const SIZE_12 = '12';

    public $id;

    /** @var string */
    public $size;

    /**
     * @return string
     */
    abstract public function label();

    /**
     * @return string
     */
    abstract public function render();
}
