<?php namespace modules\ui\widgets\data_table\assets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class DataTableAsset extends AssetBundle
{
    public $sourcePath = '@modules/ui/widgets/data_table/assets/source';

    public $js = [
        'data-table.js',
    ];

    public $depends = [
        JqueryAsset::class,
    ];
}