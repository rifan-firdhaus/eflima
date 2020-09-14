<?php namespace modules\account\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class StaffCommentAsset extends AssetBundle
{
    public $sourcePath = '@modules/account/assets/admin/source';

    public $js = [
        'js/staff-comment.js',
    ];

    public $depends = [
        JqueryAsset::class,
    ];
}