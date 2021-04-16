<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Menu;

/**
 * @var View   $this
 * @var string $active
 */

if (!isset($active)) {
    $active = 'task-setting';
}

echo $this->block('@begin');

echo Menu::widget([
    'active' => $active,
    'items' => [
        'task-setting' => [
            'label' => Yii::t('app', 'General'),
            'url' => ['/core/admin/setting/index', 'section' => 'task'],
            'visible' => Yii::$app->user->can('admin.setting.task.general')
        ],
        'task-status' => [
            'label' => Yii::t('app', 'Status'),
            'url' => ['/task/admin/task-status/index'],
            'visible' => Yii::$app->user->can('admin.setting.task.task-status.list')
        ],
        'task-priority' => [
            'label' => Yii::t('app', 'Priority'),
            'url' => ['/task/admin/task-priority/index'],
            'visible' => Yii::$app->user->can('admin.setting.task.task-priority.list')
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

echo $this->block('@end');
