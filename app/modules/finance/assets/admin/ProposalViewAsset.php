<?php namespace modules\finance\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;
use modules\ui\assets\SortableJSAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProposalViewAsset extends AssetBundle
{
    public $sourcePath = '@modules/finance/assets/admin/source';

    public $css = [
        'css/invoice-view.css',
        'css/proposal-view.css',
    ];

    public $js = [
        'js/proposal-view.js',
    ];

    public $depends = [
        MainAsset::class,
        SortableJSAsset::class,
    ];
}
