<?php namespace modules\core\validators;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\base\DynamicModel;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class DateRangeValidator extends Validator
{
    public $dateTo;
    public $endValidation = [];
    public $startValidation = [];
    public $format;
    public $type = DateValidator::TYPE_DATE;
    public $fullDay = false;

    public function validateAttribute($model, $attribute)
    {
        $dateModel = new DynamicModel([
            'date_start' => $model->{$attribute},
            'date_end' => $model->{$this->dateTo},
        ]);

        $this->startValidation['format'] = $this->endValidation['format'] = $this->format;
        $this->startValidation['type'] = $this->endValidation['type'] = $this->type;
        $dateFromEmpty = $this->isEmpty($model->{$attribute});
        $dateToEmpty = $this->isEmpty($model->{$this->dateTo});
        $fromRule = [
            'skipOnError' => true,
            'toBeginningOfDay' => $this->fullDay,
        ];
        $toRule = [
            'skipOnError' => true,
            'toEndOfDay' => $this->fullDay,
        ];

        if (!$dateFromEmpty) {
            $toRule['min'] = $model->{$attribute};
        }

        if (!$dateToEmpty) {
            $fromRule['max'] = $model->{$this->dateTo};
        }

        $dateModel->addRule('date_end', 'date', ArrayHelper::merge($toRule, $this->endValidation));
        $dateModel->addRule('date_start', 'date', ArrayHelper::merge($fromRule, $this->startValidation));

        if (!$dateModel->validate()) {
            foreach ($dateModel->errors AS $attr => $error) {
                $attr = $attr == 'date_end' ? $this->dateTo : $attribute;
                $model->addErrors([$attr => $error]);
            }

            return;
        }

        $model->{$attribute} = $dateModel->date_start;
        $model->{$this->dateTo} = $dateModel->date_end;
    }
}