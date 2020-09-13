<?php

use modules\account\web\admin\View;
use modules\support\models\Ticket;
use modules\ui\widgets\Menu;

/**
 * @var View   $this
 * @var string $active
 * @var Ticket $model
 * @var string $content
 */

if (!isset($active)) {
    $active = 'detail';
}

$this->title = $model->subject;
$this->icon = 'i8:two-tickets';
$this->menu->active = "main/support/ticket";
$this->menu->breadcrumbs[] = [
    'label' => Yii::t('app', 'View'),
    'icon' => 'i8:eye',
];

$this->fullHeightContent = true;

echo $this->block('@begin');
?>
<div class="d-flex h-100 flex-column">
    <?php
    echo Menu::widget([
        'active' => $active,
        'id' => 'ticket-view-menu',
        'items' => [
            'detail' => [
                'label' => Yii::t('app', 'Detail'),
                'url' => ['/support/admin/ticket/view', 'id' => $model->id],
                'icon' => 'i8:checked',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
            ],
            'task' => [
                'label' => Yii::t('app', 'Task'),
                'url' => ['/support/admin/ticket/view', 'id' => $model->id, 'action' => 'task'],
                'icon' => 'i8:timer',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
            ],
            'history' => [
                'label' => Yii::t('app', 'History'),
                'url' => ['/support/admin/ticket/view', 'id' => $model->id, 'action' => 'history'],
                'icon' => 'i8:activity-history',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
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

</div>
<?= $this->block('@end'); ?>
