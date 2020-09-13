<?php

use modules\account\web\admin\View;
use modules\address\models\forms\city\CitySearch;
use modules\core\components\SettingRenderer;
use yii\data\ActiveDataProvider;

/**
 * @var View               $this
 * @var CitySearch         $searchModel
 * @var SettingRenderer    $renderer
 */

$this->menu->active = "setting/address";
$activeMenu = $this->menu->getItem($this->menu->active);

if ($activeMenu) {
    $this->subTitle = $activeMenu['label'];
}

$this->beginContent('@modules/core/views/admin/setting/components/layout.php', compact('renderer'));

echo $this->block('@begin');
echo $this->render('/admin/setting/menu', [
    'active' => 'city',
]);
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();
