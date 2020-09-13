<?php namespace modules\ui\widgets\form\fields\traits;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\helpers\Html;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait RequiredTrait
{
    public $requiredCssClass = 'required';
    public $required = false;

    public function required($isRequired = true)
    {
        $this->required = $isRequired;
    }

    public function normalizeRequired()
    {
        if ($this->required) {
            Html::addCssClass($this->options, $this->requiredCssClass);
        }
    }
}