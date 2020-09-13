<?php namespace modules\finance\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\base\InputWidget;
use modules\finance\assets\admin\TaxValueAsset;
use modules\finance\models\queries\TaxQuery;
use modules\finance\models\Tax;
use yii\helpers\Html;
use modules\ui\widgets\JQueryWidgetTrait;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaxValueInput extends InputWidget
{
    use JQueryWidgetTrait;

    /** @var TaxQuery */
    public $taxQuery;
    public $jsOptions = [];

    public function run()
    {
        $this->normalize();
        $this->registerAssets();

        return Html::hiddenInput(Html::getInputName($this->model,$this->attribute)).Html::tag('div', '', $this->options);
    }

    public function registerAssets()
    {
        TaxValueAsset::register($this->view);

        $this->registerPlugin('taxValue');
    }

    public function normalize()
    {
        if (!isset($this->taxQuery)) {
            $this->taxQuery = Tax::find();
        }

        Html::removeCssClass($this->options, 'form-control');

        $this->jsOptions['inputName'] = $this->hasModel() ? Html::getInputName($this->model,$this->attribute) : $this->name;
        $this->jsOptions['taxes'] = $this->taxQuery->all();
    }
}