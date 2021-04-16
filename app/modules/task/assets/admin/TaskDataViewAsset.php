<?php namespace modules\task\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskDataViewAsset extends AssetBundle
{
    public $sourcePath = '@modules/task/assets/admin/source';

    public $js = [
        'js/task-data-view.js',
    ];

    public $depends = [
        MainAsset::class,
    ];
}
