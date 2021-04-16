<?php

use modules\account\web\admin\View;
use modules\task\models\Task;
use modules\ui\widgets\Menu;

/**
 * @var View   $this
 * @var string $active
 * @var Task   $model
 * @var string $content
 */

if (!isset($active)) {
    $active = 'detail';
}

$this->fullHeightContent = true;
$this->title = $model->title;
$this->icon = 'i8:checked';
$this->menu->active = "main/task";

echo $this->block('@begin');

?>
<div class="d-flex h-100 flex-column">
    <?php
    echo Menu::widget([
        'active' => $active,
        'id' => 'task-view-menu',
        'items' => [
            'detail' => [
                'label' => Yii::t('app', 'Task'),
                'url' => ['/task/admin/task/detail', 'id' => $model->id],
                'icon' => 'i8:checked',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.task.view.detail')
            ],
            'timer' => [
                'label' => Yii::t('app', 'Timesheet'),
                'url' => ['/task/admin/task/timer', 'id' => $model->id],
                'icon' => 'i8:timer',
                'visible' => ($model->is_timer_enabled || $model->getTimers()->exists()) && Yii::$app->user->can('admin.task.view.timer'),
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
            ],
            'history' => [
                'label' => Yii::t('app', 'History'),
                'url' => ['/task/admin/task/history', 'id' => $model->id],
                'icon' => 'i8:activity-history',
                'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                'visible' => Yii::$app->user->can('admin.task.view.history')
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

<?php echo $this->block('@end'); ?>
