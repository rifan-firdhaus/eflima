<?php namespace modules\ui\widgets\inputs;

use modules\ui\assets\SpectrumAsset;
use modules\ui\widgets\JQueryWidgetTrait;
use yii\helpers\Html;
use yii\widgets\InputWidget;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class SpectrumInput extends InputWidget
{
    use JQueryWidgetTrait;

    public $jsOptions = [
        'showInput' => true,
        'allowEmpty' => true,
        'preferredFormat' => "hex",
    ];

    public function init()
    {
        parent::init();

        $this->registerAssets();
    }

    protected function registerAssets()
    {
        $this->registerPlugin('spectrum');

        SpectrumAsset::register($this->view);
    }

    public function run()
    {
        $this->options['type'] = 'color';

        if (!$this->hasModel()) {
            return Html::textInput($this->name, $this->value, $this->options);
        }

        return Html::activeTextInput($this->model, $this->attribute, $this->options);
    }
}