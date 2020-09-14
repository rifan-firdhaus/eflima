<?php namespace modules\note\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;
use modules\file_manager\assets\FileUploaderAsset;
use modules\ui\assets\MasonryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class NoteAsset extends AssetBundle
{
    public $sourcePath = '@modules/note/assets/admin/source';

    public $js = [
        'js/note.js',
    ];

    public $css = [
        'css/note.css',
    ];

    public $depends = [
        MainAsset::class,
        MasonryAsset::class,
        FileUploaderAsset::class
    ];
}