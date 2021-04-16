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
$this->menu->active = 'main/crm/customer';

if (!isset($active)) {
    $active = 'profile';
}

$this->title = $model->name;
$this->menu->active = 'main/crm/customer';
$this->icon = 'i8:contacts';
$this->fullHeightContent = true;

echo $this->block('@begin');


?>

<div class="d-flex h-100 flex-column">
    <?php
    echo Menu::widget([
        'active' => $active,
        'id' => 'customer-view-menu',
        'items' => [
            'profile' => [
                'label' => Yii::t('app', 'Detail'),
                'url' => ['/crm/admin/customer/detail', 'id' => $model->id],
                'icon' => 'i8:contacts',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.customer.view.detail')
            ],
            'contact' => [
                'label' => Yii::t('app', 'Contact'),
                'url' => ['/crm/admin/customer/contact', 'id' => $model->id],
                'icon' => 'i8:address-book',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.customer.view.contact')
            ],
            'task' => [
                'label' => Yii::t('app', 'Task'),
                'url' => ['/crm/admin/customer/task', 'id' => $model->id],
                'icon' => 'i8:checked',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.customer.view.task')
            ],
            'event' => [
                'label' => Yii::t('app', 'Event'),
                'url' => ['/crm/admin/customer/event', 'id' => $model->id],
                'icon' => 'i8:event',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.customer.view.event')
            ],
            'history' => [
                'label' => Yii::t('app', 'History'),
                'url' => ['/crm/admin/customer/history', 'id' => $model->id],
                'icon' => 'i8:activity-history',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'order' => 99,
                'visible' => Yii::$app->user->can('admin.customer.view.history')
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
