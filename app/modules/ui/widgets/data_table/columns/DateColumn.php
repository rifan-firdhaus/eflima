<?php namespace modules\ui\widgets\data_table\columns;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\validators\DateValidator;
use yii\helpers\Html;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class DateColumn extends DataColumn
{
    public $format = 'raw';
    public $type = DateValidator::TYPE_DATETIME;

    /**
     * @inheritdoc
     */
    public function renderContent($model, $id, $index)
    {
        $value = $this->getColumnValue($model, $id, $index);

        switch ($this->type){
            case DateValidator::TYPE_DATE:
                $date = Yii::$app->formatter->asDate($value);
                break;
            case DateValidator::TYPE_TIME:
                $date = Yii::$app->formatter->asTime($value);
                break;
            default:
                $date = Yii::$app->formatter->asDatetime($value);
        }

        $date = Html::tag('div', $date,['class' => 'text-nowrap']);
        $relativeTime = Html::tag(
            'div',
            Yii::$app->formatter->asRelativeTime($value),
            ['class' => 'data-table-secondary-text text-nowrap']
        );

        return $date . $relativeTime;
    }
}