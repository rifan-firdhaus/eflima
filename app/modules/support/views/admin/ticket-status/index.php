<?php

use modules\account\web\admin\View;
use modules\core\components\SettingRenderer;
use modules\support\models\forms\ticket_status\TicketStatusSearch;

/**
 * @var View               $this
 * @var TicketStatusSearch $searchModel
 * @var SettingRenderer    $renderer
 */

$this->menu->active = "setting/ticket";
$activeMenu = $this->menu->getItem($this->menu->active);

if ($activeMenu) {
    $this->subTitle = $activeMenu['label'];
}

$this->beginContent('@modules/core/views/admin/setting/components/layout.php', compact('renderer'));

echo $this->block('@begin');
echo $this->render('/admin/setting/menu', [
    'active' => 'ticket-status',
]);
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();