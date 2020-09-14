<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class NotifyAsset extends AssetBundle
{
    public $sourcePath = '@npm/notifyjs-browser/dist';

    public $js = [
        'notify.js',
    ];

    public $depends = [
        JqueryAsset::class,
    ];
}