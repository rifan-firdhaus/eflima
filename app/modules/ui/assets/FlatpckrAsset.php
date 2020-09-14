<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class FlatpckrAsset extends AssetBundle
{
    public $sourcePath = '@npm/flatpickr/dist';
    public $plugins = [];

    public $js = [
        YII_ENV_DEV ? 'flatpickr.js' : 'flatpickr.min.js',
    ];

    public $css = [
        YII_ENV_DEV ? 'flatpickr.css' : 'flatpickr.min.css',
    ];

    public $depends = [
        JqueryAsset::class,
    ];
}