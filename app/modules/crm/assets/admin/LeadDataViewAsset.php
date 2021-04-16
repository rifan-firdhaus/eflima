<?php namespace modules\crm\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class LeadDataViewAsset extends AssetBundle
{
    public $sourcePath = '@modules/crm/assets/admin/source';

    public $js = [
        'js/lead-data-view.js',
    ];

    public $depends = [
        MainAsset::class,
    ];
}
