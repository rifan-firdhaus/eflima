<?php

use modules\account\web\admin\View;
use modules\task\models\forms\task_status\TaskStatusSearch;

/**
 * @var View             $this
 * @var TaskStatusSearch $searchModel
 */

$this->menu->active = "setting/task";
$activeMenu = $this->menu->getItem($this->menu->active);

if ($activeMenu) {
    $this->subTitle = $activeMenu['label'];
}

$this->beginContent('@modules/core/views/admin/setting/components/layout.php');

echo $this->block('@begin');
echo $this->render('/admin/setting/menu', [
    'active' => 'task-status',
]);
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();
