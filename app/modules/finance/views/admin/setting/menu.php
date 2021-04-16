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
            'visible' => Yii::$app->user->can('admin.setting.finance.general')
        ],
        'currency' => [
            'label' => Yii::t('app', 'Currency'),
            'url' => ['/finance/admin/currency/index'],
            'visible' => Yii::$app->user->can('admin.setting.finance.currency.list')
        ],
        'tax' => [
            'label' => Yii::t('app', 'Tax'),
            'url' => ['/finance/admin/tax/index'],
            'visible' => Yii::$app->user->can('admin.setting.finance.tax.list')
        ],
        'expense-category' => [
            'label' => Yii::t('app', 'Expense Category'),
            'url' => ['/finance/admin/expense-category/index'],
            'visible' => Yii::$app->user->can('admin.setting.finance.expense-category.list')
        ],
        'proposal-status' => [
            'label' => Yii::t('app', 'Proposal Status'),
            'url' => ['/finance/admin/proposal-status/index'],
            'visible' => Yii::$app->user->can('admin.setting.finance.proposal-status.list')
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
