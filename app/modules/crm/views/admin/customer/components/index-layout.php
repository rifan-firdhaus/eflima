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

echo Menu::widget([
    'active' => $active,
    'items' => [
        'index' => [
            'label' => Yii::t('app', 'Customer'),
            'url' => ['/crm/admin/customer/index'],
            'icon' => 'i8:contacts',
            'iconOptions' => ['class' => 'icon icons8-size mr-1'],
        ],
        'contact' => [
            'label' => Yii::t('app', 'Contact'),
            'url' => ['/crm/admin/customer-contact/index'],
            'icon' => 'i8:address-book',
            'iconOptions' => ['class' => 'icon icons8-size mr-1'],
        ],
        'history' => [
            'label' => Yii::t('app', 'History'),
            'url' => ['/crm/admin/customer/history'],
            'icon' => 'i8:activity-history',
            'iconOptions' => ['class' => 'icon icons8-size mr-1'],
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

echo $content;

echo $this->block('@end');
