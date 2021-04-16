<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Menu;

/**
 * @var View   $this
 * @var string $active
 * @var string $content
 */

if (!isset($active)) {
    $active = 'index';
}

echo $this->block('@begin');

$this->fullHeightContent = true;
?>
    <div class="d-flex h-100 flex-column">
        <?php
        echo Menu::widget([
            'active' => $active,
            'items' => [
                'index' => [
                    'label' => Yii::t('app', 'List'),
                    'url' => ['/task/admin/task/index'],
                    'icon' => 'i8:checked',
                    'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                    'visible' => Yii::$app->user->can('admin.task.list')
                ],
                'timer' => [
                    'label' => Yii::t('app', 'Timesheet'),
                    'url' => ['/task/admin/task-timer/index'],
                    'icon' => 'i8:timer',
                    'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                    'visible' => Yii::$app->user->can('admin.task.timer.list')
                ],
                'history' => [
                    'label' => Yii::t('app', 'History'),
                    'url' => ['/task/admin/task/all-history'],
                    'icon' => 'i8:activity-history',
                    'iconOptions' => ['class' => 'icon icons8-size mr-1'],
                    'visible' => Yii::$app->user->can('admin.task.history')
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
<?php

echo $this->block('@end');
