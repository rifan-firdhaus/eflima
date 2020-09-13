<?php namespace modules\ui\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\base\InputWidget;
use modules\ui\assets\Select2Asset;
use yii\helpers\Html;
use modules\ui\widgets\JQueryWidgetTrait;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Select2Input extends InputWidget
{
    use JQueryWidgetTrait;

    public $selected;
    public $source = [];
    public $multiple = false;
    public $prompt;
    public $allowClear = false;
    public $jsOptions = [
        'width' => '100%',
    ];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->normalize();
        $this->registerAssets();

        if (!$this->hasModel()) {
            return Html::dropDownList($this->name, $this->value, $this->source, $this->options);
        }

        return Html::activeDropDownList($this->model, $this->attribute, $this->source, $this->options);
    }

    /**
     * @return void
     */
    public function normalize()
    {
        if ($this->allowClear && !isset($this->prompt)) {
            $this->prompt = '';
        }

        if (isset($this->prompt)) {
            $this->jsOptions['placeholder'] = [
                'id' => '',
                'text' => $this->prompt,
            ];

            $this->options['prompt'] = $this->prompt;
        }

        $useTags = ArrayHelper::getValue($this->jsOptions, 'tags', false);

        if ($this->multiple) {
            $this->options['multiple'] = true;
        }

        $this->jsOptions['allowClear'] = $this->allowClear;

        $value = $this->model ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;

        if ($value !== '' && !is_null($value) &&
            (!empty($this->jsOptions['ajax']['url']) || $useTags) &&
            is_callable($this->selected)
        ) {
            $selected = call_user_func($this->selected, $value, $this);

            if($selected){
                $this->source = $selected;
            }
        }
    }

    public function registerAssets()
    {
        Select2Asset::register($this->view);

        $this->registerPlugin('select2');
    }
}