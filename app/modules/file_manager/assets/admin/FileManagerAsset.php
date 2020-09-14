<?php namespace modules\file_manager\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class FileManagerAsset extends AssetBundle
{
    public $sourcePath = '@modules/ui/assets/admin/source';

    public $js = [
        'js/file-manager.js',
    ];

    public $css = [
        'css/file-manager.css',
    ];

    public $depends = [
        MainAsset::class,
    ];
}
