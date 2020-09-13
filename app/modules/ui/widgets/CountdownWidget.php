<?php namespace modules\ui\widgets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\base\Widget;
use modules\ui\assets\CountdownAsset;
use yii\helpers\Html;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CountdownWidget extends Widget
{
    use JQueryWidgetTrait;

    public $options = [];
    public $jsOptions = [];

    public $since;
    public $until;

    public function run()
    {
        if ($this->since) {
            $this->jsOptions['since'] = $this->since;
        }

        if ($this->until) {
            $this->jsOptions['since'] = $this->until;
        }

        $this->registerAssets();

        return Html::tag('div', '', $this->options);
    }

    public function registerAssets()
    {
        CountdownAsset::register($this->view);

        $this->registerPlugin('countdown');
    }
}