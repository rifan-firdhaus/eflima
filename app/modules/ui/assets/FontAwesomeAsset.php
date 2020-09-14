<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/npm-asset/font-awesome';

    public $css = [
        YII_ENV_DEV ? 'css/all.css' : 'css/all.min.css',
    ];
}