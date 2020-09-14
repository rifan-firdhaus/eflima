<?php namespace modules\calendar\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class EventViewAsset extends AssetBundle
{
    public $sourcePath = '@modules/calendar/assets/admin/source';

    public $js = [
        'js/event-view.js',
    ];

    public $css = [
        'css/event-view.css',
    ];

    public $depends = [
        JqueryAsset::class,
    ];
}
