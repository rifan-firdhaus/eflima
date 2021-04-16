<?php

use modules\account\web\admin\View;
use modules\finance\models\Expense;
use modules\ui\widgets\Menu;

/**
 * @var View    $this
 * @var string  $active
 * @var Expense $model
 * @var string  $content
 */

if (!isset($active)) {
    $active = 'detail';
}

$this->title = $model->name;
$this->icon = 'i8:money-transfer';
$this->menu->active = "main/transaction/expense";
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
        'id' => 'expense-view-menu',
        'items' => [
            'detail' => [
                'label' => Yii::t('app', 'Detail'),
                'url' => ['/finance/admin/expense/detail', 'id' => $model->id],
                'icon' => 'i8:money-transfer',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.expense.view.detail'),
            ],
            'task' => [
                'label' => Yii::t('app', 'Task'),
                'url' => ['/finance/admin/expense/task', 'id' => $model->id],
                'icon' => 'i8:checked',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.expense.view.task'),
            ],
            'history' => [
                'label' => Yii::t('app', 'History'),
                'url' => ['/finance/admin/expense/history', 'id' => $model->id],
                'icon' => 'i8:activity-history',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.expense.view.history'),
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
