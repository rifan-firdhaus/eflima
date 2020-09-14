<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class FilesizeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/avoidwork/filesize.js/lib';

    public $js = [
        YII_ENV_DEV ? 'filesize.js' : 'filesize.min.js',
    ];
}