<?php

use modules\account\web\admin\View;
use modules\address\models\forms\province\ProvinceSearch;
use modules\core\components\SettingRenderer;

/**
 * @var View            $this
 * @var ProvinceSearch  $searchModel
 * @var SettingRenderer $renderer
 */

$this->menu->active = "setting/address";
$activeMenu = $this->menu->getItem($this->menu->active);

if ($activeMenu) {
    $this->subTitle = $activeMenu['label'];
}

$this->beginContent('@modules/core/views/admin/setting/components/layout.php', compact('renderer'));

echo $this->block('@begin');
echo $this->render('/admin/setting/menu', [
    'active' => 'province',
]);
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();
