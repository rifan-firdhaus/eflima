<?php namespace modules\address\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class FlagIconAsset extends AssetBundle
{
    public $sourcePath = '@npm/flag-icon-css';

    public $css = [
        YII_ENV_DEV ? 'css/flag-icon.css' : 'css/flag-icon.min.css',
    ];
}