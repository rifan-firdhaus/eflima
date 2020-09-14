<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CountdownAsset extends AssetBundle
{
    public $sourcePath = '@npm/kbw-countdown/dist';

    public $css = [
        'css/jquery.countdown.css',
    ];

    public $js = [
        'js/jquery.plugin.js',
        'js/jquery.countdown.js',
    ];

    public $depends = [
        JqueryAsset::class,
    ];
}