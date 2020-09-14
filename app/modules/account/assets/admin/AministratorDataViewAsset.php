<?php namespace modules\account\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class AministratorDataViewAsset extends AssetBundle
{
    public $sourcePath = '@modules/account/assets/admin/source';

    public $js = [
        'js/staff-data-view.js',
    ];

    public $depends = [
        MainAsset::class,
    ];
}