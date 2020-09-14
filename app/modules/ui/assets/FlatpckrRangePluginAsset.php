<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class FlatpckrRangePluginAsset extends AssetBundle
{
    public $sourcePath = '@npm/flatpickr/dist';
    public $plugins = [];

    public $js = [
       'plugins/rangePlugin.js'
    ];

    public $depends = [
        FlatpckrAsset::class,
    ];
}