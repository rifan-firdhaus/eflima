<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class FancyTreeAsset extends AssetBundle
{
    public $sourcePath = '@npm/jquery.fancytree';

    public $js = [
        YII_ENV_DEV ? 'dist/jquery.fancytree-all-deps.js' : 'dist/jquery.fancytree-all-deps.min.js',
    ];

    public $css = [
        YII_ENV_DEV ? 'dist/skin-awesome/ui.fancytree.css' : 'dist/skin-awesome/ui.fancytree.min.css',
    ];

    public $depends = [
        JqueryAsset::class,
    ];
}