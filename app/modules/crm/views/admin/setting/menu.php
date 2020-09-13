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
        ],
        'customer-group' => [
            'label' => Yii::t('app', 'Customer Group'),
            'url' => ['/crm/admin/customer-group/index'],
        ],
        'lead-source' => [
            'label' => Yii::t('app', 'Lead Source'),
            'url' => ['/crm/admin/lead-source/index'],
        ],
        'lead-status' => [
            'label' => Yii::t('app', 'Lead Status'),
            'url' => ['/crm/admin/lead-status/index'],
        ],
        'lead-follow-up-type' => [
            'label' => Yii::t('app', 'Lead Follow Up'),
            'url' => ['/crm/admin/lead-follow-up-type/index'],
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
