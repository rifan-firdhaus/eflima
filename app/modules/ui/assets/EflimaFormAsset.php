<?php namespace modules\ui\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\validators\ValidationAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class EflimaFormAsset extends AssetBundle
{
    public $sourcePath = '@modules/ui/assets/source';

    public $js = [
        'js/eflima-form.js',
    ];

    public $depends = [
        ValidationAsset::class,
    ];
}