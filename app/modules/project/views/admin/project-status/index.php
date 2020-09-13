<?php

use modules\account\web\admin\View;
use modules\core\components\SettingRenderer;
use modules\project\models\forms\project_status\ProjectStatusSearch;

/**
 * @var View                $this
 * @var ProjectStatusSearch $searchModel
 * @var SettingRenderer     $renderer
 */

$this->menu->active = "setting/project";
$activeMenu = $this->menu->getItem($this->menu->active);

if ($activeMenu) {
    $this->subTitle = $activeMenu['label'];
}

$this->beginContent('@modules/core/views/admin/setting/components/layout.php', compact('renderer'));

echo $this->block('@begin');
echo $this->render('/admin/setting/menu', [
    'active' => 'project-status',
]);
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();