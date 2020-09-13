<?php namespace modules\core\validators;

use Yii;
use yii\validators\DateValidator as BaseDateValidator;
use function strtotime;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class DateValidator extends BaseDateValidator
{
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';
    const TYPE_DATETIME = 'datetime';

    public $toEndOfDay = false;
    public $toBeginningOfDay = false;

    public $convertToTimestamp = true;
    public $type = self::TYPE_DATE;

    /** @inheritdoc */
    public function init()
    {
        $this->normalize();

        parent::init();
    }

    /**
     * Normalize property
     */
    public function normalize()
    {
        $setting = Yii::$app->setting;

        $this->timestampAttributeTimeZone = $setting->get('timezone');

        if ($this->type && $this->format === null) {
            switch ($this->type) {
                case self::TYPE_DATE:
                    $this->format = $setting->get('date_input_format');
                    break;
                case self::TYPE_DATETIME;
                    $this->format = $setting->get('date_input_format') . ' ' . substr($setting->get('time_input_format'), 4);
                    break;
                case self::TYPE_TIME:
                    $this->format = $setting->get('time_input_format');
                    break;
            }
        }
    }

    /** @inheritdoc */
    public function validateAttribute($model, $attribute)
    {
        if ($this->convertToTimestamp) {
            $this->timestampAttribute = $attribute;
        }

        if ($this->toBeginningOfDay || $this->toEndOfDay) {
            $value = $model->$attribute;

            $timestamp = $this->parseDateValue($value);

            if ($this->toBeginningOfDay) {
                $model->{$attribute} = strtotime(date('Y-m-d 00:00:00', $timestamp));
            }

            if ($this->toEndOfDay) {
                $model->{$attribute} = strtotime(date('Y-m-d 23:59:59', $timestamp));
            }
        }

        parent::validateAttribute($model, $attribute);
    }
}