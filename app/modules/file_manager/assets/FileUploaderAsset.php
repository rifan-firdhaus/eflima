<?php namespace modules\file_manager\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use modules\ui\assets\FilesizeAsset;
use yii\bootstrap4\BootstrapAsset;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class FileUploaderAsset extends AssetBundle
{
    public $sourcePath = '@modules/ui/assets/source';

    public $js = [
        'file-uploader/file-uploader.js',
    ];

    public $css = [
        'file-uploader/file-uploader.css',
    ];

    public $depends = [
        FilesizeAsset::class,
        BootstrapAsset::class,
        JqueryAsset::class
    ];
}