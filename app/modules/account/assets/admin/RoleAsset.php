<?php namespace modules\account\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use modules\ui\assets\FancyTreeAsset;
use modules\ui\assets\FontAwesomeAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class RoleAsset extends AssetBundle
{
    public $sourcePath = '@modules/account/assets/admin/source';

    public $js = [
        'js/role.js',
        'js/permission.js',
    ];

    public $depends = [
        MainAsset::class,
        FontAwesomeAsset::class,
        FancyTreeAsset::class,
    ];
}
