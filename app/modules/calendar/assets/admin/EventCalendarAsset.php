<?php namespace modules\calendar\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use modules\ui\assets\FullCalendarAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class EventCalendarAsset extends AssetBundle
{
    public $sourcePath = '@modules/calendar/assets/admin/source';

    public $js = [
        'js/event-calendar.js',
    ];

    public $depends = [
        FullCalendarAsset::class,
    ];
}