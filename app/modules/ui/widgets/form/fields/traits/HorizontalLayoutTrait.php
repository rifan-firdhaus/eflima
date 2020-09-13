<?php namespace modules\ui\widgets\form\fields\traits;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait HorizontalLayoutTrait
{
    public $layout;
    public $horizontalCssClasses = [];

    /**
     * @inheritdoc
     */
    protected function normalizeHorizontalLayout()
    {
        if (!$this->layout) {
            $this->layout = $this->form->layout;
        }

        if ($this->layout === self::LAYOUT_HORIZONTAL) {
            $this->horizontalCssClasses = ArrayHelper::merge([
                'field' => 'form-row',
                'label' => 'col-sm-3 col-form-label',
                'input' => 'col-sm-9',
            ], $this->horizontalCssClasses);

            if (!is_array($this->inputWrapperOptions)) {
                $this->inputWrapperOptions = [];
            }

            Html::addCssClass($this->inputWrapperOptions, $this->horizontalCssClasses['input']);
            Html::addCssClass($this->labelOptions, $this->horizontalCssClasses['label']);
            Html::addCssClass($this->options, $this->horizontalCssClasses['field']);
        }
    }
}