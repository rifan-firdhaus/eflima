<?php namespace modules\support\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;
use modules\ui\assets\PrismAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TicketReplyFormAsset extends AssetBundle
{
    public $sourcePath = '@modules/support/assets/admin/source';

    public $js = [
        'js/ticket-reply-form.js',
    ];

    public $css = [
        'css/ticket-reply-form.css',
    ];

    public $depends = [
        MainAsset::class,
        PrismAsset::class
    ];
}