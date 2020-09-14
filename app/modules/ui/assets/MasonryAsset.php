<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class MasonryAsset extends AssetBundle
{
    public $sourcePath = '@npm/masonry-layout/dist';

    public $js = [
        'masonry.pkgd.min.js',
    ];

    public $depends = [
        JqueryAsset::class,
    ];
}