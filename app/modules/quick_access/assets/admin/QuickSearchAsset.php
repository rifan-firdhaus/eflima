<?php namespace modules\quick_access\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class QuickSearchAsset extends AssetBundle
{
    public $sourcePath = '@modules/quick_access/assets/admin/source';

    public $js = [
        'js/quick-search.js',
    ];

    public $css = [
        'css/quick-search.css',
    ];

    public $depends = [
        MainAsset::class,
    ];
}