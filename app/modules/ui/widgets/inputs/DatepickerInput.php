<?php namespace modules\ui\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\Setting;
use modules\core\validators\DateValidator;
use modules\ui\assets\FlatpckrAsset;
use modules\ui\assets\FlatpckrRangePluginAsset;
use yii\helpers\Html;
use modules\ui\widgets\JQueryWidgetTrait;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class DatepickerInput extends InputWidget
{
    use JQueryWidgetTrait;

    public $type = DateValidator::TYPE_DATE;
    public $jsOptions = [
        'language' => 'en',
        'autoClose' => true,
    ];

    public $range = false;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->normalize();
        $this->registerAssets();

        if ($this->hasModel()) {
            return Html::activeTextInput($this->model, $this->attribute, $this->options);
        }

        return Html::textInput($this->name, $this->value, $this->options);
    }

    /**
     * @throws InvalidConfigException
     */
    public function normalize()
    {
        /** @var Setting $setting */
        $setting = Yii::$app->setting;
        $dateFormat = $setting->get('date_input_format');
        $value = $this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;


        if ($this->type === DateValidator::TYPE_DATETIME) {
            $timeFormat = $setting->get('time_input_format');
            $dateFormat = $dateFormat . ' ' . substr($timeFormat, 4);
            $this->jsOptions['enableTime'] = true;
            $this->jsOptions['autoClose'] = false;

            if (isset($value) && $value !== '') {
                $this->options['value'] = Yii::$app->formatter->asDatetime($value, $dateFormat);
            }
        }

        if (isset($value) && $value !== '') {
            $this->options['value'] = Yii::$app->formatter->asDate($value, $dateFormat);
        }

        $this->jsOptions['dateFormat'] = strtr(substr($dateFormat, 4), [
            'a' => 'A',
            'A' => 'K',
        ]);

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }

        if ($this->range) {
            $rangeOptions = Json::encode($this->range);

            $this->jsOptions['plugins'][] = new JsExpression("new rangePlugin({$rangeOptions})");
        }

        $this->options['autocomplete'] = 'off';
    }

    /**
     * @return void
     */
    public function registerAssets()
    {
        FlatpckrAsset::register($this->view);

        if ($this->range) {
            FlatpckrRangePluginAsset::register($this->view);
        }

        $this->registerPlugin('flatpickr');
    }
}