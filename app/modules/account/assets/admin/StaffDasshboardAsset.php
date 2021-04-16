<?php namespace modules\account\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use modules\ui\assets\MasonryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class StaffDasshboardAsset extends AssetBundle
{
    public $sourcePath = '@modules/account/assets/admin/source';

    public $css = [
        'css/staff-dashboard.css',
    ];

    public $depends = [
        MainAsset::class,
        MasonryAsset::class,
    ];
}
