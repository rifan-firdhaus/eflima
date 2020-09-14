<?php namespace modules\task\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use modules\ui\assets\SortableJSAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskCheckListAsset extends AssetBundle
{
    public $sourcePath = '@modules/task/assets/admin/source';

    public $css = [
        'css/task-check-list.css',
    ];

    public $js = [
        'js/task-check-list.js',
    ];

    public $depends = [
        TaskAsset::class,
        SortableJSAsset::class,
    ];
}