<?php namespace modules\ui\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\helpers\Html;
use Yii;
use yii\widgets\MaskedInput;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class NumericInput extends MaskedInput
{
    public $clientOptions = [
        'alias' => 'decimal',
        'autoGroup' => true,
        'rightAlign' => false,
    ];

    public $autoUnMasked = true;

    public $originalInputOptions = [];

    public function init()
    {
        parent::init();

        $formatter = Yii::$app->formatter;

        if (!isset($this->clientOptions['radixPoint'])) {
            $this->clientOptions['radixPoint'] = '.';
        }

        if (!isset($this->clientOptions['groupSeparator'])) {
            $this->clientOptions['groupSeparator'] = ',';
        }

        $originalName = $this->hasModel() ? Html::getInputName($this->model, $this->attribute) : $this->name;

        $this->originalInputOptions['name'] = $originalName;
        $this->originalInputOptions['id'] = $this->options['id'];

        $this->options['name'] = '';
        $this->options['id'] = $this->options['id'] . '-alias';
    }

    public function registerClientScript()
    {
        parent::registerClientScript();

        if ($this->autoUnMasked) {
            $aliasId = $this->options['id'];
            $originalId = $this->originalInputOptions['id'];

            $js = "$('#{$aliasId}').on('change keyup',function(){ $('#{$originalId}').val($(this).inputmask('unmaskedvalue')).trigger('change') })";

            $this->view->registerJs($js);
        }
    }

    public function renderInputHtml($type)
    {
        Html::addCssClass($this->options, 'numeric-input');
        Html::addCssClass($this->originalInputOptions, 'numeric-input-original');

        if ($this->autoUnMasked) {
            $aliasInput = parent::renderInputHtml($type);

            if ($this->hasModel()) {
                $originalInput = Html::activeHiddenInput($this->model, $this->attribute, $this->originalInputOptions);
            } else {
                $originalInput = Html::hiddenInput($this->name, $this->value, $this->originalInputOptions);
            }

            return $originalInput.$aliasInput;
        }

        return parent::renderInputHtml($type);
    }
}