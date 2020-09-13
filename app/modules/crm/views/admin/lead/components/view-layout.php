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
        'id' => 'customer-view-menu',
        'items' => [
            'profile' => [
                'label' => Yii::t('app', 'Detail'),
                'url' => ['/crm/admin/lead/view', 'id' => $model->id],
                'icon' => 'i8:connect',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
            ],
            'task' => [
                'label' => Yii::t('app', 'Task'),
                'url' => ['/crm/admin/lead/view', 'id' => $model->id, 'action' => 'task'],
                'icon' => 'i8:checked',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
            ],
            'event' => [
                'label' => Yii::t('app', 'Event'),
                'url' => ['/crm/admin/lead/view', 'id' => $model->id, 'action' => 'event'],
                'icon' => 'i8:event',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
            ],
            'history' => [
                'label' => Yii::t('app', 'History'),
                'url' => ['/crm/admin/lead/view', 'id' => $model->id, 'action' => 'history'],
                'icon' => 'i8:activity-history',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'order' => 99,
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
