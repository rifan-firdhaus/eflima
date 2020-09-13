<?php namespace modules\ui\widgets\form\fields\traits;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait ErrorTrait
{
    public $invalidCssClass = 'is-invalid';
    public $validCssClass = 'is-valid';
    public $errorOptions = [
        'class' => 'invalid-tooltip',
        'tag' => 'div',
    ];
    public $errors = [];

    /**
     * @return string;
     */
    public function error()
    {
        if ($this->errors === false) {
            return '';
        }

        $tag = ArrayHelper::remove($this->errorOptions, 'tag', 'div');
        $firstError = isset($this->errors[0]) ? $this->errors[0] : '';

        return Html::tag($tag, $firstError, $this->errorOptions);
    }

    /**
     * @param string $message
     */
    public function addError($message)
    {
        $this->errors[] = $message;
    }

    /**
     * @param string[] $messages
     */
    public function addErrors($messages)
    {
        array_walk($messages, [$this, 'addError']);
    }

    /**
     * @return void
     */
    public function normalizeError()
    {
        if (!empty($this->errors)) {
            Html::addCssClass($this->inputOptions, $this->invalidCssClass);
        }
    }
}