<?php namespace modules\support\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TicketReplyAsset extends AssetBundle
{
    public $sourcePath = '@modules/support/assets/admin/source';

    public $css = [
        'css/ticket-reply.css',
    ];

    public $depends = [
        MainAsset::class,
    ];
}