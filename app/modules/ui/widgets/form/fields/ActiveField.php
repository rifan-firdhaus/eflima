<?php namespace modules\ui\widgets\form\fields;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\helpers\Html;
use yii\base\Model;
use yii\validators\Validator;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ActiveField extends RegularField
{
    /** @var Model */
    public $model;

    /** @var string */
    public $attribute;

    public $enableClientValidation;
    public $enableAjaxValidation;

    public function init()
    {
        if ($this->model === null) {
            $this->model = $this->form->model;
        }

        parent::init();
    }

    /**
     * @return string
     */
    public function asInput()
    {
        return Html::activeInput($this->type, $this->model, $this->attribute, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asText()
    {
        return Html::activeTextInput($this->model, $this->attribute, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asPassword()
    {
        return Html::activePasswordInput($this->model, $this->attribute, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asTextarea()
    {
        return Html::activeTextarea($this->model, $this->attribute, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asCheckbox()
    {
        return Html::activeCheckbox($this->model, $this->attribute, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asRadio()
    {
        return Html::activeRadio($this->model, $this->attribute, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asCheckboxList()
    {
        return Html::activeCheckboxList($this->model, $this->attribute, $this->source, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asDropDownList()
    {
        return Html::activeDropDownList($this->model, $this->attribute, $this->source, $this->inputOptions);
    }

    /**
     * @return string
     */
    public function asRadioList()
    {
        return Html::activeRadioList($this->model, $this->attribute, $this->source, $this->inputOptions);
    }

    /**
     * @return array
     */
    public function getFieldJsOptions()
    {
        $options = parent::getFieldJsOptions();

        $attribute = Html::getAttributeName($this->attribute);

        if (!in_array($attribute, $this->model->activeAttributes(), true)) {
            return $options;
        }

        $clientValidation = $this->isClientValidationEnabled();
        $ajaxValidation = $this->isAjaxValidationEnabled();

        if ($clientValidation) {
            $validators = [];

            foreach ($this->model->getActiveValidators($attribute) as $validator) {
                /* @var $validator Validator */

                $js = $validator->clientValidateAttribute($this->model, $attribute, $this->form->getView());

                if ($validator->enableClientValidation && $js != '') {
                    if ($validator->whenClient !== null) {
                        $js = "if (({$validator->whenClient})(attribute, value)) { $js }";
                    }

                    $validators[] = $js;
                }
            }
        }

        if ($ajaxValidation) {
            $options['ajaxValidation'] = true;
        }

        if (!empty($validators)) {
            $options['validate'] = new JsExpression('function (attribute, value, messages, deferred, $form) {' . implode('', $validators) . '}');
        }

        return $options;
    }

    /**
     * Checks if ajax validation enabled for the field.
     *
     * @return boolean
     */
    protected function isClientValidationEnabled()
    {
        return $this->enableClientValidation || $this->enableClientValidation === null && $this->form->enableClientValidation;
    }

    /**
     * Checks if ajax validation enabled for the field.
     *
     * @return boolean
     */
    protected function isAjaxValidationEnabled()
    {
        return $this->enableAjaxValidation || $this->enableAjaxValidation === null && $this->form->enableAjaxValidation;
    }

    /**
     * @inheritdoc
     */
    public function normalize()
    {
        if (empty($this->errors) && $this->errors !== false) {
            $error = $this->model->getFirstError($this->attribute);

            if ($error) {
                $this->addError($error);
            }
        }

        $attribute = Html::getAttributeName($this->attribute);

        if ($this->type === self::TYPE_WIDGET) {
            $this->widget['attribute'] = $this->attribute;
            $this->widget['model'] = $this->model;
        }

        if (!isset($this->inputOptions['id'])) {
            $this->inputOptions['id'] = Html::getInputId($this->model, $this->attribute);
        }

        if(!isset($this->inputOptions['data-rid'])){
            $this->inputOptions['data-rid'] = Html::getRealInputId($this->model, $this->attribute);
        }

        if ($this->label === null) {
            $this->label = $this->model->getAttributeLabel($attribute);
        }

        if ($this->hint === null) {
            $this->hint = $this->model->getAttributeHint($attribute);
        }

        $this->required = $this->model->isAttributeRequired($attribute);

        if ($this->placeholder === true) {
            $this->placeholder = $this->label;
        }

        parent::normalize();
    }
}