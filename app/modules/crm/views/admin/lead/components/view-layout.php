<?php

use modules\account\web\admin\View;
use modules\crm\models\Customer;
use modules\ui\widgets\Menu;

/**
 * @var View     $this
 * @var string   $active
 * @var string   $content
 * @var Customer $model
 */

if (!isset($active)) {
    $active = 'profile';
}

$this->title = $model->name;
$this->menu->active = 'main/crm/lead';
$this->icon = 'i8:connect';
$this->fullHeightContent = true;

echo $this->block('@begin');


?>

<div class="d-flex h-100 flex-column">
    <?php
    echo Menu::widget([
        'active' => $active,
        'id' => 'lead-view-menu',
        'items' => [
            'profile' => [
                'label' => Yii::t('app', 'Detail'),
                'url' => ['/crm/admin/lead/detail', 'id' => $model->id],
                'icon' => 'i8:connect',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.lead.view.detail')
            ],
            'task' => [
                'label' => Yii::t('app', 'Task'),
                'url' => ['/crm/admin/lead/task', 'id' => $model->id],
                'icon' => 'i8:checked',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.lead.view.task')
            ],
            'event' => [
                'label' => Yii::t('app', 'Event'),
                'url' => ['/crm/admin/lead/event', 'id' => $model->id],
                'icon' => 'i8:event',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.lead.view.event')
            ],
            'history' => [
                'label' => Yii::t('app', 'History'),
                'url' => ['/crm/admin/lead/history', 'id' => $model->id],
                'icon' => 'i8:activity-history',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'order' => 99,
                'visible' => Yii::$app->user->can('admin.lead.view.history')
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
