<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Menu;

/**
 * @var View   $this
 * @var string $active
 */

if (!isset($active)) {
    $active = 'country';
}

echo $this->block('@begin');

echo Menu::widget([
    'active' => $active,
    'id' => 'setting-address-menu',
    'items' => [
        'country' => [
            'label' => Yii::t('app', 'Country'),
            'url' => ['/address/admin/country/index'],
            'visible' => Yii::$app->user->can('admin.setting.country.list')
        ],
        'province' => [
            'label' => Yii::t('app', 'Province'),
            'url' => ['/address/admin/province/index'],
            'visible' => Yii::$app->user->can('admin.setting.province.list')
        ],
        'city' => [
            'label' => Yii::t('app', 'City'),
            'url' => ['/address/admin/city/index'],
            'visible' => Yii::$app->user->can('admin.setting.city.list')
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
