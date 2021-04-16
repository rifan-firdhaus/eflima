<?php namespace modules\account\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\AssetBundle;
use modules\ui\assets\NotifyAsset;
use modules\ui\assets\NProgressAsset;
use modules\ui\widgets\lazy\assets\LazyAsset;
use yii\bootstrap4\BootstrapPluginAsset;
use yii\web\JqueryAsset;
use yii\web\YiiAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class StaffDataViewAsset extends AssetBundle
{
    public $sourcePath = '@modules/account/assets/admin/source';

    public $js = [
        'js/staff-data-view.js',
    ];
    public $depends = [
        MainAsset::class
    ];
}
