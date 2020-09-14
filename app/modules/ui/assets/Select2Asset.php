<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Select2Asset extends AssetBundle
{
    public $sourcePath = '@npm/select2/dist';

    public $js = [
        YII_ENV_DEV ? 'js/select2.full.js' : 'js/select2.full.min.js',
    ];

    public $css = [
        YII_ENV_DEV ? 'css/select2.css' : 'css/select2.min.css',
    ];

    public $depends = [
        JqueryAsset::class,
    ];
}