<?php namespace modules\ui\widgets\form\fields;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class RegularField extends InputField
{
    public $source = [];

    /**
     * @return string
     */
    public function asInput()
    {
        return Html::input($this->type, $this->name, $this->value, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asText()
    {
        return Html::textInput($this->name, $this->value, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asPassword()
    {
        return Html::passwordInput($this->name, $this->value, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asTextarea()
    {
        return Html::textarea($this->name, $this->value, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asCheckbox()
    {
        return Html::checkbox($this->name, $this->value, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asRadio()
    {
        return Html::radio($this->name, $this->value, $this->inputOptions);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function asWidget()
    {
        /** @var InputWidget $class */
        $class = $this->widget['class'];

        unset($this->widget['class']);

        if (is_subclass_of($class, InputWidget::class)) {
            $this->widget['field'] = $this;
            $this->widget['options'] = $this->inputOptions;
        }

        return $class::widget($this->widget);
    }

    /**
     * @return string
     */
    public function asCheckboxList()
    {
        return Html::checkboxList($this->name, $this->value, $this->source, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asDropDownList()
    {
        return Html::dropDownList($this->name, $this->value, $this->source, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asRadioList()
    {
        return Html::radioList($this->name, $this->value, $this->source, $this->inputOptions);
    }

    /**
     * @inheritdoc
     */
    public function input()
    {
        switch ($this->type) {
            case self::TYPE_TEXT:
                $input = $this->asText();
                break;

            case self::TYPE_TEXTAREA:
                $input = $this->asTextarea();
                break;

            case self::TYPE_CHECKBOX:
                $input = $this->asCheckbox();
                break;

            case self::TYPE_RADIO:
                $input = $this->asRadio();
                break;

            case self::TYPE_DROP_DOWN_LIST:
                $input = $this->asDropDownList();
                break;

            case self::TYPE_CHECKBOX_LIST:
                $input = $this->asCheckboxList();
                break;

            case self::TYPE_RADIO_LIST:
                $input = $this->asRadioList();
                break;

            case self::TYPE_PASSWORD:
                $input = $this->asPassword();
                break;

            case self::TYPE_WIDGET:
                $input = $this->asWidget();
                break;

            default:
                $input = $this->asInput();
        }

        return $input;
    }

    public function normalize()
    {
        $this->inputOptions['placeholder'] = $this->placeholder;

        parent::normalize();
    }
}