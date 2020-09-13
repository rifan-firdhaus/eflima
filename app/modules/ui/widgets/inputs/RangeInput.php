<?php namespace modules\ui\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\base\InputWidget;
use modules\ui\assets\IonRangeSliderAsset;
use yii\helpers\Html;
use modules\ui\widgets\JQueryWidgetTrait;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class RangeInput extends InputWidget
{
    use JQueryWidgetTrait;

    public $jsOptions = [];
    public $min = 0;
    public $max;

    public function init()
    {
        parent::init();

        $this->registerAssets();
    }

    protected function registerAssets()
    {
        if (isset($this->max)) {
            $this->jsOptions['max'] = $this->max;
        }

        $this->jsOptions['min'] = $this->min;

        $this->registerPlugin('ionRangeSlider');

        IonRangeSliderAsset::register($this->view);
    }

    /**
     * @return string
     */
    public function run()
    {
        return $this->renderInputHtml('text');
    }
}