<?php namespace modules\core\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use NumberFormatter;
use Yii;
use yii\i18n\Formatter as BaseFormatter;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Formatter extends BaseFormatter
{
    public $numberFormatterOptions = [
        NumberFormatter::MAX_FRACTION_DIGITS => 10,
        NumberFormatter::MIN_FRACTION_DIGITS => 0
    ];

    public function asShortDuration($time)
    {
        if (!$time) {
            $time = 0;
        }

        if ($time < 60) {
            return Yii::t('app', '{number} seconds', ['number' => $time]);
        } else {
            $minute = round($time / 60, 2);

            if ($minute < 60) {
                return Yii::t('app', '{number} minutes', ['number' => $minute]);
            } else {
                return Yii::t('app', '{number} hours', ['number' => round($time / 3600, 2)]);
            }
        }
    }

    public function asCurrency($value, $currency = null, $options = [], $textOptions = [])
    {
        $value = floatval($value);

        return parent::asCurrency($value, $currency, $options, $textOptions);
    }
}