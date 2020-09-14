<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class IonRangeSliderAsset extends AssetBundle
{
    public $sourcePath = '@npm/ion-rangeslider';

    public $js = [
        YII_ENV_DEV ? 'js/ion.rangeSlider.js' : 'js/ion.rangeSlider.min.js',
    ];

    public $css = [
        YII_ENV_DEV ? 'css/ion.rangeSlider.css' : 'css/ion.rangeSlider.min.css',
    ];

    public $depends = [
        JqueryAsset::class,
    ];
}