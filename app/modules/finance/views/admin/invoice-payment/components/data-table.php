<?php

use modules\account\web\admin\View;
use modules\crm\models\Customer;
use modules\finance\models\forms\invoice_payment\InvoicePaymentSearch;
use modules\finance\models\InvoicePayment;
use modules\ui\widgets\data_table\columns\ActionColumn;
use modules\ui\widgets\data_table\columns\CheckboxColumn;
use modules\ui\widgets\data_table\columns\DateColumn;
use modules\ui\widgets\data_table\DataTable;
use modules\ui\widgets\Icon;
use modules\ui\widgets\table\cells\Cell;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View                 $this
 * @var ActiveDataProvider   $dataProvider
 * @var array                $dataTableOptions
 * @var InvoicePaymentSearch $searchModel
 */
$baseCurrency = Yii::$app->setting->get('finance/base_currency');
$isInInvoice = !empty($searchModel->params['invoice_id']);
$isInCustomer = !empty($searchModel->params['customer_id']);

$iconTypes = [
    Customer::TYPE_PERSONAL => 'i8:contacts',
    Customer::TYPE_COMPANY => 'i8:business-building',
];

if (!isset($dataTableOptions)) {
    $dataTableOptions = [];
}

echo $this->block('@begin', [
    'dataTableOptions' => &$dataTableOptions,
]);

$dataTable = DataTable::begin(ArrayHelper::merge([
    'dataProvider' => $dataProvider,
    'id' => 'invoice-payment-data-table',
    'card' => false,
    'linkPager' => false,
    'idAttribute' => 'id',
    'lazy' => false,
    'columns' => [
        [
            'class' => CheckboxColumn::class,
        ],
        [
            'attribute' => 'number',
            'contentCell' => [
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'format' => 'raw',
            'content' => function ($model) {
                /** @var InvoicePayment $model */
                $number = Html::a(Html::encode($model->number), ['/finance/admin/invoice-payment/view', 'id' => $model->id], [
                    'data-lazy-modal' => 'invoice-payment-form',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                    'data-lazy-modal-size' => 'modal-lg',
                    'class' => 'd-block text-monospace data-table-primary-text',
                ]);
                $date = Html::tag('div', Yii::$app->formatter->asDatetime($model->at), [
                    'class' => 'data-table-secondary-text',
                ]);

                return $number . $date;
            },
        ],
        [
            'attribute' => 'invoice_id',
            'visible' => !$isInInvoice,
            'format' => 'raw',
            'content' => function ($model) {
                /** @var InvoicePayment $model */

                $invoice = Html::a('#' . Html::encode($model->invoice->number), ['/finance/admin/invoice/view', 'id' => $model->invoice_id], [
                    'class' => 'd-block text-monospace',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'invoice-view-modal',
                ]);
                $date = Html::tag('div', Yii::$app->formatter->asDate($model->invoice->date), [
                    'class' => 'data-table-secondary-text',
                ]);

                return $invoice . $date;
            },
        ],
        [
            'attribute' => 'invoice.customer_id',
            'visible' => !$isInInvoice && !$isInCustomer,
            'format' => 'raw',
            'content' => function ($model) use ($iconTypes) {
                /** @var InvoicePayment $model */

                if (!$model->invoice->customer_id) {
                    return '';
                }

                $type = Icon::show($iconTypes[$model->invoice->customer->type], [
                    'class' => 'icon icons8-size',
                    'title' => Yii::t('app', $model->invoice->customer->typeText),
                    'data-toggle' => 'tooltip',
                ]);
                $name = Html::a($type . Html::encode($model->invoice->customer->company_name), ['/crm/admin/customer/view', 'id' => $model->invoice->customer_id], [
                    'class' => 'd-block',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'customer-view-modal',
                ]);

                if ($model->invoice->customer->type === Customer::TYPE_COMPANY) {
                    $primaryContact = Html::tag('div', Html::encode($model->invoice->customer->primaryContact->name), ['class' => 'data-table-secondary-text']);
                    $name .= $primaryContact;
                }

                return $name;
            },
        ],
        [
            'attribute' => 'accepted_at',
            'class' => DateColumn::class,
        ],
        [
            'attribute' => 'method_id',
            'contentCell' => [
                'vAlign' => Cell::V_ALIGN_CENTER,
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'content' => function ($model) {
                /** @var InvoicePayment $model */

                return $model->method->getLabel();
            },
        ],
        [
            'attribute' => 'amount',
            'format' => 'raw',
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_RIGHT,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_RIGHT,
            ],
            'content' => function ($model) use ($baseCurrency) {
                /** @var InvoicePayment $model */

                $value = Html::tag('div', Yii::$app->formatter->asCurrency($model->amount, $model->invoice->currency_code), [
                    'class' => 'data-table-primary-text',
                ]);

                if ($model->invoice->currency_code !== $baseCurrency) {
                    $realValue = Html::tag('div', Yii::$app->formatter->asCurrency($model->real_amount, $baseCurrency), [
                        'class' => 'data-table-secondary-text',
                    ]);

                    return $value . $realValue;
                }

                return $value;
            },
        ],
        [
            'class' => ActionColumn::class,
            'sort' => 1000000,
            'controller' => '/finance/admin/invoice-payment',
            'buttons' => [
                'view' => [
                    'value' => [
                        'icon' => 'i8:eye',
                        'name' => Yii::t('app', 'View'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal-size' => 'modal-lg',
                        'data-lazy-modal' => 'invoice-payment-view-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'update' => false,
                'delete' => false,
            ],
        ],
    ],
], $dataTableOptions));

$dataTable->table->footer->addRow([
    'cells' => [
        'number' => [
            'format' => 'raw',
            'content' => Html::tag('div', Yii::t('app', 'Total'), [
                'class' => 'data-table-primary-text font-size-lg',
            ]),
        ],
        'amount' => [
            'format' => 'raw',
            'content' => Html::tag('div', Yii::$app->formatter->asCurrency($searchModel->totalAmount), [
                'class' => 'data-table-primary-text font-size-lg',
            ]),
            'hAlign' => Cell::H_ALIGN_RIGHT,
            'vAlign' => Cell::V_ALIGN_CENTER,
        ],
    ],
]);

echo $this->block('@data-table');

DataTable::end();

echo $this->block('@end', $dataTable);