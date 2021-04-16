<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Menu;

/**
 * @var View   $this
 * @var string $active
 * @var string $content
 */

$this->title = Yii::t('app', 'Lead');
$this->menu->active = "main/crm/lead";
$this->icon = 'i8:connect';

if (!isset($active)) {
    $active = 'index';
}

$this->fullHeightContent = true;

echo $this->block('@begin');
?>
<div class="d-flex h-100 flex-column">
    <?= Menu::widget([
        'active' => $active,
        'items' => [
            'index' => [
                'label' => Yii::t('app', 'Leads'),
                'url' => ['/crm/admin/lead/index'],
                'icon' => 'i8:connect',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.lead.list'),
            ],
            'kanban' => [
                'label' => Yii::t('app', 'Kanban'),
                'url' => ['/crm/admin/lead-status/kanban'],
                'icon' => 'i8:address-book',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.lead.kanban'),
            ],
            'history' => [
                'label' => Yii::t('app', 'History'),
                'url' => ['/crm/admin/lead/all-history'],
                'icon' => 'i8:activity-history',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.lead.history'),
            ],
        ],
        'options' => [
            'class' => 'nav nav-pills nav-pills-main',
        ],
        'linkOptions' => [
            'class' => 'nav-link',
        ],
        'itemOptions' => [
            'class' => 'nav-item',
        ],
    ]);
    ?>

    <div class="h-100 overflow-auto">
        <?= $content; ?>
    </div>

    <?= $this->block('@end'); ?>
</div>
