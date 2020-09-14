<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TinyMceAsset extends AssetBundle
{
    public $sourcePath = '@npm/tinymce';

    public $js = [
        'tinymce.min.js',
        'jquery.tinymce.min.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}