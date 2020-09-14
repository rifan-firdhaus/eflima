<?php namespace modules\ui\widgets\lazy\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class LazyAsset extends AssetBundle
{
    public $sourcePath = '@modules/ui/widgets/lazy/assets/source';

    public $js = [
        'historyjs/scripts/bundled/html5/native.history.js',
        'lazy-v2.js',
    ];

    public $depends = [
        JqueryAsset::class,
    ];
}