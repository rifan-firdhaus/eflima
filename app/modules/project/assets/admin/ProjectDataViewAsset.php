<?php namespace modules\project\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;
use modules\ui\assets\Select2Asset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProjectDataViewAsset extends AssetBundle
{
    public $sourcePath = '@modules/project/assets/admin/source';

    public $js = [
        'js/project-data-view.js',
    ];

    public $depends = [
        MainAsset::class,
    ];
}
