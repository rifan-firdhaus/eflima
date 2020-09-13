<?php

use modules\account\web\admin\View;
use modules\core\components\SettingRenderer;
use modules\finance\models\forms\currency\CurrencySearch;

/**
 * @var View            $this
 * @var CurrencySearch  $searchModel
 * @var SettingRenderer $renderer
 */

$this->menu->active = "setting/finance";
$activeMenu = $this->menu->getItem($this->menu->active);

if ($activeMenu) {
    $this->subTitle = $activeMenu['label'];
}

$this->beginContent('@modules/core/views/admin/setting/components/layout.php', compact('renderer'));

echo $this->block('@begin');
echo $this->render('/admin/setting/menu', [
    'active' => 'currency',
]);
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();