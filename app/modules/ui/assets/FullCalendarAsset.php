<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\bootstrap4\BootstrapAsset;
use yii\bootstrap4\BootstrapPluginAsset;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class FullCalendarAsset extends AssetBundle
{
    public $sourcePath = '@npm/fullcalendar/dist';

    public $js = [
        'core/main.js',
        'daygrid/main.js',
        'interaction/main.js',
        'timegrid/main.js',
        'bootstrap/main.js',
    ];

    public $css = [
        'core/main.css',
        'daygrid/main.css',
        'timegrid/main.css',
        'bootstrap/main.css',
    ];

    public $depends = [
        JqueryAsset::class,
        BootstrapAsset::class
    ];
}