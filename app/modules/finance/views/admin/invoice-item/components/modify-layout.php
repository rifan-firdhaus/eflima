<?php

use modules\account\web\admin\View;
use modules\finance\assets\admin\InvoiceItemModifyAsset;
use modules\finance\models\Invoice;
use modules\ui\widgets\Menu;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * @var View    $this
 * @var string  $active
 * @var string  $content
 * @var Invoice $model
 */

InvoiceItemModifyAsset::register($this);

if (!isset($active)) {
    $active = 'default';
}

$temp = Yii::$app->request->get('temp');

echo $this->block('@begin');

echo Html::beginTag('div', [
    'id' => "invoice-item-modify-container-{$this->uniqueId}",
    'data-rid' => 'invoice-item-modify-container',
]);

echo Menu::widget([
    'active' => $active,
    'id' => 'invoice-item-modify-menu',
    'items' => [
        'default' => [
            'label' => Yii::t('app', 'Product'),
            'url' => [
                '/finance/admin/invoice-item/add',
                'invoice_id' => !$temp ? $model->id : null,
                'temp' => $temp,
            ],
            'linkOptions' => [
                'data-lazy' => 0,
            ],
        ],
        'expense' => [
            'label' => Yii::t('app', 'Expense'),
            'url' => [
                '/finance/admin/expense/billable-picker',
                'invoice_id' => !$temp ? $model->id : null,
                'temp' => $temp,
            ],
            'linkOptions' => [
                'data-lazy' => 0,
            ],
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

$jsOptions = Json::encode([
    'models' => Json::decode(Yii::$app->request->post('models')),
    'model' => Json::decode(Yii::$app->request->post('model')),
    'invoice' => Json::decode(Yii::$app->request->post('invoice')),
]);

$this->registerJs("$('#invoice-item-modify-container-{$this->uniqueId}').invoiceItemModify({$jsOptions})");

echo Html::endTag('div');

echo $this->block('@end');