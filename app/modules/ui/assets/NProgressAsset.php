<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class NProgressAsset extends AssetBundle
{
    public $sourcePath = '@npm/nprogress';

    public $js = [
        'nprogress.js',
    ];

    public $css = [
        'nprogress.css',
    ];

    public $depends = [
        JqueryAsset::class,
    ];
}