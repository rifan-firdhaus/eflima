<?php

use modules\account\web\admin\View;
use modules\finance\assets\admin\InvoiceViewAsset;
use modules\finance\models\Invoice;
use modules\ui\widgets\Menu;

/**
 * @var View    $this
 * @var Invoice $model
 * @var string  $content
 * @var string  $active
 */

InvoiceViewAsset::register($this);

if (!isset($active)) {
    $active = 'detail';
}

$this->title = '#' . $model->number;
$this->icon = 'i8:cash';
$this->menu->active = 'main/transaction/invoice';

$this->fullHeightContent = true;
?>
<div class="d-flex flex-column h-100">
    <?= Menu::widget([
        'active' => $active,
        'items' => [
            'detail' => [
                'label' => Yii::t('app', 'Detail'),
                'url' => ['/finance/admin/invoice/detail', 'id' => $model->id],
                'icon' => 'i8:cash',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.invoice.view.detail')
            ],
            'payment' => [
                'label' => Yii::t('app', 'Payments'),
                'url' => ['/finance/admin/invoice/payment', 'id' => $model->id],
                'icon' => 'i8:receive-cash',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.invoice.view.payment')
            ],
            'task' => [
                'label' => Yii::t('app', 'Tasks'),
                'url' => ['/finance/admin/invoice/task', 'id' => $model->id],
                'icon' => 'i8:checked',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.invoice.view.task')
            ],
            'history' => [
                'label' => Yii::t('app', 'History'),
                'url' => ['/finance/admin/invoice/history', 'id' => $model->id],
                'icon' => 'i8:activity-history',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'order' => 99,
                'visible' => Yii::$app->user->can('admin.invoice.view.history')
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
        <?= $content ?>
    </div>
</div>

