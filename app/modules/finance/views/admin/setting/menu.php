<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Menu;

/**
 * @var View   $this
 * @var string $active
 */

if (!isset($active)) {
    $active = 'finance-setting';
}

echo $this->block('@begin');

echo Menu::widget([
    'active' => $active,
    'items' => [
        'finance-setting' => [
            'label' => Yii::t('app', 'General'),
            'url' => ['/core/admin/setting/index', 'section' => 'finance'],
        ],
        'currency' => [
            'label' => Yii::t('app', 'Currency'),
            'url' => ['/finance/admin/currency/index'],
        ],
        'tax' => [
            'label' => Yii::t('app', 'Tax'),
            'url' => ['/finance/admin/tax/index'],
        ],
        'expense-category' => [
            'label' => Yii::t('app', 'Expense Category'),
            'url' => ['/finance/admin/expense-category/index'],
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