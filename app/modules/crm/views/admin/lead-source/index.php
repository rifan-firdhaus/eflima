<?php

use modules\account\web\admin\View;
use modules\core\components\SettingRenderer;
use modules\crm\models\forms\lead_source\LeadSourceSearch;

/**
 * @var View             $this
 * @var LeadSourceSearch $searchModel
 * @var SettingRenderer  $renderer
 */

$this->menu->active = "setting/crm";
$activeMenu = $this->menu->getItem($this->menu->active);

if ($activeMenu) {
    $this->subTitle = $activeMenu['label'];
}

$this->beginContent('@modules/core/views/admin/setting/components/layout.php', compact('renderer'));

echo $this->block('@begin');
echo $this->render('/admin/setting/menu', [
    'active' => 'lead-source',
]);
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();