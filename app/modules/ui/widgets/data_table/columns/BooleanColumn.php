<?php namespace modules\ui\widgets\data_table\columns;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use Yii;
use yii\bootstrap4\ButtonDropdown;
use yii\helpers\ArrayHelper;
use function call_user_func;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class BooleanColumn extends DataColumn
{
    public $updatable = true;
    public $trueLabel;
    public $falseLabel;
    public $trueUrl;
    public $falseUrl;
    public $url;
    public $trueItemOptions = [];
    public $falseItemOptions = [];
    public $format = 'raw';
    public $buttonOptions = [];
    public $trueActionLabel;
    public $falseActionLabel;

    public function init()
    {
        if (!$this->trueLabel) {
            $this->trueLabel = Yii::t('app', 'Yes');
        }

        if (!$this->falseLabel) {
            $this->falseLabel = Yii::t('app', 'No');
        }

        parent::init();
    }

    protected function renderContent($model, $id, $index)
    {
        $value = (boolean) $this->getColumnValue($model, $id, $index);
        $label = $value ? $this->trueLabel : $this->falseLabel;

        if (!$this->updatable) {
            return $label;
        }

        $falseUrl = $this->falseUrl;
        $trueUrl = $this->trueUrl;

        if ($this->url instanceof Closure) {
            if (!$falseUrl) {
                $falseUrl = call_user_func($this->url, false, $model, $id, $index);
            }

            if (!$trueUrl) {
                $trueUrl = call_user_func($this->url, true, $model, $id, $index);
            }
        } else {
            if (!$falseUrl) {
                $falseUrl = $this->url;
            }

            if (!$trueUrl) {
                $trueUrl = $this->url;
            }
        }

        $buttonOptions = $this->buttonOptions;
        $trueItemOptions = $this->trueItemOptions;
        $falseItemOptions = $this->falseItemOptions;

        if ($falseItemOptions instanceof Closure) {
            $falseItemOptions = call_user_func($falseItemOptions, $value, $model, $id, $index);
        }

        if ($trueItemOptions instanceof Closure) {
            $trueItemOptions = call_user_func($trueItemOptions, $value, $model, $id, $index);
        }

        if ($buttonOptions instanceof Closure) {
            $buttonOptions = call_user_func($buttonOptions, $value, $model, $id, $index);
        }

        $falseItem = ArrayHelper::merge([
            'label' => $this->falseActionLabel ? $this->falseActionLabel : $this->falseLabel,
            'url' => $falseUrl,
            'encode' => false,
            'visible' => $value,
        ], $falseItemOptions);

        $trueItem = ArrayHelper::merge([
            'label' => $this->trueActionLabel ? $this->trueActionLabel : $this->trueLabel,
            'url' => $trueUrl,
            'encode' => false,
            'visible' => !$value,
        ], $trueItemOptions);

        $buttonOptions = ArrayHelper::merge([
            'label' => $label,
            'tagName' => 'a',
            'dropdown' => [
                'items' => [$falseItem, $trueItem],
            ],
        ], $buttonOptions);

        return ButtonDropdown::widget($buttonOptions);
    }
}