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

$this->title = $model->name;
$this->menu->active = 'main/project';
$this->icon = 'i8:idea';

if (!isset($active)) {
    $active = 'project';
}

$this->fullHeightContent = true;

echo $this->block('@begin');

?>

<div class="d-flex h-100 flex-column">
    <?= Menu::widget([
        'active' => $active,
        'id' => 'project-view-menu',
        'items' => [
            'project' => [
                'label' => Yii::t('app', 'Project'),
                'url' => ['/project/admin/project/detail', 'id' => $model->id],
                'icon' => 'i8:idea',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
            ],
            'task' => [
                'label' => Yii::t('app', 'Task'),
                'url' => ['/project/admin/project/task', 'id' => $model->id],
                'icon' => 'i8:checked',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.project.view.task'),
            ],
            'milestone' => [
                'label' => Yii::t('app', 'Milestone'),
                'url' => ['/project/admin/project/milestone', 'id' => $model->id],
                'icon' => 'i8:slider',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.project.view.milestone.list'),
            ],
            'task-timer' => [
                'label' => Yii::t('app', 'Timesheet'),
                'url' => ['/project/admin/project/task-timer','id' => $model->id],
                'icon' => 'i8:timer',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.project.view.task-timer'),
            ],
            'transaction' => [
                'label' => Yii::t('app', 'Transaction'),
                'icon' => 'i8:money-transfer',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'items' => [
                    [
                        'label' => Yii::t('app', 'Invoice'),
                        'icon' => 'i8:cash',
                        'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                        'url' => ['/project/admin/project/invoice', 'id' => $model->id],
                        'visible' => Yii::$app->user->can('admin.project.view.invoice'),
                    ],
                    [
                        'label' => Yii::t('app', 'Payment'),
                        'icon' => 'i8:receive-cash',
                        'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                        'url' => ['/project/admin/project/payment', 'id' => $model->id],
                        'visible' => Yii::$app->user->can('admin.project.view.payment'),
                    ],
                    [
                        'label' => Yii::t('app', 'Expense'),
                        'icon' => 'i8:money-transfer',
                        'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                        'url' => ['/project/admin/project/expense', 'id' => $model->id],
                        'visible' => Yii::$app->user->can('admin.project.view.expense'),
                    ],
                ],
            ],
            'ticket' => [
                'label' => Yii::t('app', 'Ticket'),
                'url' => ['/project/admin/project/ticket', 'id' => $model->id],
                'icon' => 'i8:two-tickets',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.project.view.ticket'),
            ],
            'event' => [
                'label' => Yii::t('app', 'Event'),
                'url' => ['/project/admin/project/event', 'id' => $model->id],
                'icon' => 'i8:event',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.project.view.event'),
            ],
            'discussion' => [
                'label' => Yii::t('app', 'Discussion'),
                'url' => ['/project/admin/project/discussion','id' => $model->id],
                'icon' => 'i8:chat',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.project.view.discussion.view'),
            ],
            'history' => [
                'label' => Yii::t('app', 'History'),
                'url' => ['/project/admin/project/history', 'id' => $model->id],
                'icon' => 'i8:activity-history',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'order' => 99,
                'visible' => Yii::$app->user->can('admin.project.view.history'),
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
