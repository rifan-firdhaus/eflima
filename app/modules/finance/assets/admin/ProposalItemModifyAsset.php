<?php namespace modules\finance\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProposalItemModifyAsset extends AssetBundle
{
    public $sourcePath = '@modules/finance/assets/admin/source';

    public $js = [
        'js/proposal-item-modify.js',
    ];

    public $depends = [
        MainAsset::class,
    ];
}
