<?php namespace modules\account\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class UnauthenticatedAsset extends AssetBundle
{
    public $sourcePath = '@modules/account/assets/admin/source';

    public $css = [
        'css/unauthenticated.css',
    ];

    public $js = [

    ];

    public $depends = [
        JqueryAsset::class,
    ];
}