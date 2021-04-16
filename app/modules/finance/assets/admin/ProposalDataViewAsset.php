<?php namespace modules\finance\assets\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\MainAsset;
use modules\core\web\AssetBundle;
use yii\bootstrap4\BootstrapPluginAsset;
use yii\web\JqueryAsset;
use yii\widgets\MaskedInputAsset;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProposalDataViewAsset extends AssetBundle
{
    public $sourcePath = '@modules/finance/assets/admin/source';

    public $js = [
        'js/proposal-data-view.js',
    ];

    public $depends = [
        MainAsset::class
    ];
}
