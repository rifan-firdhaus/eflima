<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Menu;

/**
 * @var View   $this
 * @var string $active
 */

if (!isset($active)) {
    $active = 'ticket-setting';
}

echo $this->block('@begin');

echo Menu::widget([
    'active' => $active,
    'items' => [
        'ticket-setting' => [
            'label' => Yii::t('app', 'General'),
            'url' => ['/core/admin/setting/index', 'section' => 'ticket'],
        ],
        'ticket-status' => [
            'label' => Yii::t('app', 'Status'),
            'url' => ['/support/admin/ticket-status/index'],
        ],
        'ticket-priority' => [
            'label' => Yii::t('app', 'Priority'),
            'url' => ['/support/admin/ticket-priority/index'],
        ],
        'ticket-department' => [
            'label' => Yii::t('app', 'Department'),
            'url' => ['/support/admin/ticket-department/index'],
        ],
        'ticket-predefined-reply' => [
            'label' => Yii::t('app', 'Predefined Reply'),
            'url' => ['/support/admin/ticket-predefined-reply/index'],
        ],
        'ticket-predefined-reply' => [
            'label' => Yii::t('app', 'Predefined Reply'),
            'url' => ['/support/admin/ticket-predefined-reply/index'],
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