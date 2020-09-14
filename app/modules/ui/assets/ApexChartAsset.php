<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ApexChartAsset extends AssetBundle
{
    public $sourcePath = '@npm/apexcharts/dist';

    public $js = [
        YII_ENV_DEV ? 'apexcharts.js' : 'apexcharts.min.js',
    ];
}