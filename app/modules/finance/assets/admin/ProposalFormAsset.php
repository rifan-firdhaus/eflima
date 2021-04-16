<?php namespace modules\finance\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;
use modules\ui\assets\SortableJSAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProposalFormAsset extends AssetBundle
{
    public $sourcePath = '@modules/finance/assets/admin/source';

    public $js = [
        'js/proposal-form.js',
    ];

    public $css = [
        'css/proposal-form.css',
        'css/invoice-view.css',
    ];

    public $depends = [
        MainAsset::class,
        SortableJSAsset::class
    ];
}
