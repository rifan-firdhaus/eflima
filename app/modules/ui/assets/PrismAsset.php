<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class PrismAsset extends AssetBundle
{
    public $sourcePath = '@modules/ui/assets/source';

    public $js = [
        'prism/prism.js',
    ];
    public $css = [
        'prism/prism.css',
    ];
}