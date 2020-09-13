<?php namespace modules\ui\widgets\form\fields;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\ui\widgets\form\fields\traits\ErrorTrait;
use modules\ui\widgets\form\fields\traits\HorizontalLayoutTrait;
use modules\ui\widgets\form\fields\traits\RequiredTrait;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\InputWidget;
use function in_array;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
abstract class InputField extends Field
{
    use HorizontalLayoutTrait;
    use RequiredTrait;
    use ErrorTrait;

    const LAYOUT_HORIZONTAL = 'horizontal';
    const LAYOUT_VERTICAL = 'vertical';

    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_PASSWORD = 'password';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIO = 'radio';
    const TYPE_DROP_DOWN_LIST = 'dropDownList';
    const TYPE_CHECKBOX_LIST = 'checkboxList';
    const TYPE_RADIO_LIST = 'radioList';
    const TYPE_WIDGET = 'widget';

    public $value;
    public $name;

    /** @var array|InputWidget */
    public $widget;

    public $type = self::TYPE_TEXT;

    public $inputGroups = [];
    public $inputGroupOptions = [
        'class' => 'input-group',
    ];
    public $inputGroupAppendOptions = [
        'class' => 'input-group-append',
    ];
    public $inputGroupPrependOptions = [
        'class' => 'input-group-prepend',
    ];

    public $standalone = false;

    public $watchEvent = ['keyup', 'change'];

    public $placeholder = false;

    /**
     * @inheritdoc
     */
    public function build()
    {
        $this->form->fields[$this->inputOptions['data-rid']] = $this->getFieldJsOptions();

        if ($this->visibility) {
            $this->form->fieldVisibility[$this->selectors['field']] = $this->buildVisibility($this->visibility);
        }

        $input = $this->input();

        if ($this->inputOnly) {
            return $input;
        }

        $input = $this->asInputGroup($input, '', $this->error());

        if ($this->standalone) {
            return $this->begin() . $input . $this->hint() . $this->end();
        }

        return $this->begin() .
            $this->label() .
            $this->wrapInput($input) .
            $this->end();
    }

    /**
     * @param string $input
     * @param string $before
     * @param string $after
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    protected function asInputGroup($input, $before = '', $after = '')
    {
        if (empty($this->inputGroups)) {
            return $before . $input . $after;
        }

        $forbiddenInputType = [
            self::TYPE_RADIO_LIST,
            self::TYPE_RADIO,
            self::TYPE_CHECKBOX_LIST,
            self::TYPE_CHECKBOX,
        ];

        if (in_array($this->type, $forbiddenInputType)) {
            throw new InvalidConfigException("Input type {$this->type} is not supposed to as input group");
        }

        $append = [];
        $prepend = [];

        foreach ($this->inputGroups AS $inputGroup) {
            $asText = ArrayHelper::getValue($inputGroup, 'asText', true);
            $content = $inputGroup['content'];

            if ($asText) {
                $tag = ArrayHelper::getValue($inputGroup, 'tag', 'span');
                $options = ArrayHelper::getValue($inputGroup, 'options', []);
                $options = ArrayHelper::merge($options, ['class' => 'input-group-text']);
                $content = Html::tag($tag, $content, $options);
            }

            ${$inputGroup['position']}[] = $content;
        }

        $parts = [];

        $parts[] = $before;

        if (!empty($prepend)) {
            $prependTag = ArrayHelper::remove($this->inputGroupPrependOptions, 'tag', 'div');

            $parts[] = Html::tag($prependTag, implode('', $prepend), $this->inputGroupPrependOptions);
        }

        $parts[] = $input;

        $parts[] = $after;

        if (!empty($append)) {
            $appendTag = ArrayHelper::remove($this->inputGroupAppendOptions, 'tag', 'div');

            $parts[] = Html::tag($appendTag, implode('', $append), $this->inputGroupAppendOptions);
        }

        $tag = ArrayHelper::remove($this->inputGroupOptions, 'tag', 'div');

        return Html::tag($tag, implode('', $parts), $this->inputGroupOptions);
    }

    /**
     * @return array
     */
    public function getFieldJsOptions()
    {
        $options = $this->selectors;

        $options['validClass'] = $this->validCssClass;
        $options['invalidClass'] = $this->invalidCssClass;
        $options['watchEvent'] = (array) $this->watchEvent;

        return $options;
    }

    /**
     * @inheritdoc
     */
    public function normalize()
    {
        if ($this->standalone || $this->inputOnly) {
            $this->layout = self::LAYOUT_VERTICAL;
        }

        if ($this->type === self::TYPE_WIDGET) {
            $this->widget['name'] = $this->name;
            $this->widget['value'] = $this->value;
            $this->widget['view'] = $this->form->getView();
            $this->widget['options'] = $this->inputOptions;
        }

        $this->normalizeRequired();
        $this->normalizeHorizontalLayout();
        $this->normalizeError();

        foreach ($this->inputGroups AS $key => $inputGroup) {
            if (is_string($inputGroup)) {
                $inputGroup = ['content' => $inputGroup];
            }

            if (!isset($inputGroup['position']) || !in_array($inputGroup['position'], ['append', 'prepend'])) {
                $inputGroup['position'] = 'prepend';
            }

            $this->inputGroups[$key] = $inputGroup;
        }

        parent::normalize();

        $this->selectors['error'] = Html::cssSelector($this->errorOptions);

        if ($this->type === self::TYPE_RADIO_LIST) {
            $this->selectors['input'] = Html::cssSelector($this->inputOptions) . ' input[type=radio]';
        } elseif ($this->type === self::TYPE_CHECKBOX_LIST) {
            $this->selectors['input'] = Html::cssSelector($this->inputOptions) . ' input[type=checkbox]';
        }

        if (in_array($this->type, [self::TYPE_RADIO_LIST, self::TYPE_RADIO, self::TYPE_CHECKBOX, self::TYPE_CHECKBOX_LIST])) {
            Html::addCssClass($this->inputOptions, ['widget' => '']);
        } else {
            Html::addCssClass($this->inputOptions, ['widget' => 'form-control']);
        }

        Html::addCssClass($this->options, ['widget' => 'form-group']);
    }
}