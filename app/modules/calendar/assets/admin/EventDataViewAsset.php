<?php namespace modules\calendar\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class EventDataViewAsset extends AssetBundle
{
    public $sourcePath = '@modules/calendar/assets/admin/source';

    public $js = [
        'js/event-data-view.js',
    ];

    public $depends = [
        MainAsset::class,
    ];
}
