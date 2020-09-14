<?php namespace modules\task\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskInteractionFormAsset extends AssetBundle
{
    public $sourcePath = '@modules/task/assets/admin/source';

    public $js = [
        'js/task-interaction-form.js',
    ];

    public $depends = [
        TaskAsset::class,
    ];
}