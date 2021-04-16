<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Menu;

/**
 * @var View   $this
 * @var string $active
 */

if (!isset($active)) {
    $active = 'customer-setting';
}

echo $this->block('@begin');

echo Menu::widget([
    'active' => $active,
    'items' => [
        'customer-setting' => [
            'label' => Yii::t('app', 'General'),
            'url' => ['/core/admin/setting/index', 'section' => 'crm'],
            'visible' => Yii::$app->user->can('admin.setting.crm.general'),
        ],
        'customer-group' => [
            'label' => Yii::t('app', 'Customer Group'),
            'url' => ['/crm/admin/customer-group/index'],
            'visible' => Yii::$app->user->can('admin.setting.crm.customer-group.list'),
        ],
        'lead-source' => [
            'label' => Yii::t('app', 'Lead Source'),
            'url' => ['/crm/admin/lead-source/index'],
            'visible' => Yii::$app->user->can('admin.setting.crm.lead-source.list'),
        ],
        'lead-status' => [
            'label' => Yii::t('app', 'Lead Status'),
            'url' => ['/crm/admin/lead-status/index'],
            'visible' => Yii::$app->user->can('admin.setting.crm.lead-status.list'),
        ],
        'lead-follow-up-type' => [
            'label' => Yii::t('app', 'Lead Follow Up'),
            'url' => ['/crm/admin/lead-follow-up-type/index'],
            'visible' => Yii::$app->user->can('admin.setting.crm.lead-follow-up-type.list'),
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
