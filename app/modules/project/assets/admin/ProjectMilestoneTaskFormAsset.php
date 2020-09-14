<?php namespace modules\project\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use modules\task\assets\admin\TaskFormAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProjectMilestoneTaskFormAsset extends AssetBundle
{
    public $sourcePath = '@modules/project/assets/admin/source';

    public $js = [
        'js/project-milestone-task-form.js'
    ];

    public $depends = [
        TaskFormAsset::class,
    ];
}