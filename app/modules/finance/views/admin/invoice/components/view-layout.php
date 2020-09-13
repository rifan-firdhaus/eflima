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
                'url' => ['/finance/admin/invoice/view', 'id' => $model->id],
                'icon' => 'i8:cash',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
            ],
            'payment' => [
                'label' => Yii::t('app', 'Payments'),
                'url' => ['/finance/admin/invoice/view', 'id' => $model->id, 'action' => 'payment'],
                'icon' => 'i8:receive-cash',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
            ],
            'task' => [
                'label' => Yii::t('app', 'Tasks'),
                'url' => ['/finance/admin/invoice/view', 'id' => $model->id, 'action' => 'task'],
                'icon' => 'i8:checked',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
            ],
            'history' => [
                'label' => Yii::t('app', 'History'),
                'url' => ['/finance/admin/invoice/view', 'id' => $model->id, 'action' => 'history'],
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
        <?= $content ?>
    </div>
</div>

