<?php namespace modules\task\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskAsset extends AssetBundle
{
    public $sourcePath = '@modules/task/assets/admin/source';

    public $css = [
        'css/task.css',
    ];

    public $js = [
        'js/task.js',
    ];

    public $depends = [
        MainAsset::class,
    ];
}