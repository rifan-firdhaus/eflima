<?php namespace modules\project\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;
use modules\ui\assets\SortableJSAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProjectMilestoneAsset extends AssetBundle
{
    public $sourcePath = '@modules/project/assets/admin/source';

    public $js = [
        'js/project-milestone.js',
    ];

    public $css = [
        'css/project-milestone.css',
    ];

    public $depends = [
        MainAsset::class,
        SortableJSAsset::class,
    ];
}