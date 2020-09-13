<?php

use modules\account\web\admin\View;
use modules\core\validators\DateValidator;
use modules\crm\models\Customer;
use modules\finance\models\Expense;
use modules\finance\models\forms\expense\ExpenseSearch;
use modules\finance\models\Invoice;
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
 * @var View               $this
 * @var ActiveDataProvider $dataProvider
 * @var array              $dataTableOptions
 * @var ExpenseSearch      $searchModel
 * @var true               $picker
 */
$baseCurrency = Yii::$app->setting->get('finance/base_currency');
$isInCustomer = !empty($searchModel->params['customer_id']);

if (!isset($picker)) {
    $picker = false;
}

if (!isset($dataTableOptions)) {
    $dataTableOptions = [];
}

echo $this->block('@begin', [
    'dataTableOptions' => &$dataTableOptions,
]);

$iconTypes = [
    Customer::TYPE_PERSONAL => 'i8:contacts',
    Customer::TYPE_COMPANY => 'i8:business-building',
];

$dataTable = DataTable::begin(ArrayHelper::merge([
    'dataProvider' => $dataProvider,
    'id' => 'expense-data-table',
    'card' => false,
    'linkPager' => false,
    'idAttribute' => 'id',
    'lazy' => false,
    'columns' => [
        [
            'class' => CheckboxColumn::class,
        ],
        [
            'attribute' => 'name',
            'contentCell' => [
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'format' => 'raw',
            'content' => function ($model) use ($picker) {
                /** @var Expense $model */

                $reference = Html::tag('div', '#' . Html::encode($model->reference), ['class' => 'text-monospace']);

                if ($picker) {
                    $name = Html::tag('div', Html::encode($model->name), [
                        'class' => 'd-block data-table-primary-text',
                    ]);

                    $category = Html::tag('div', Html::encode($model->category->name) . ' ' . $reference . '', [
                        'class' => 'd-block data-table-secondary-text',
                    ]);

                    return $name . $category;
                }

                $name = Html::a(Html::encode($model->name), ['/finance/admin/expense/view', 'id' => $model->id], [
                    'data-lazy-modal' => 'expense-form-modal',
                    'data-lazy-container' => '#main-container',
                    'class' => 'd-block data-table-primary-text',
                ]);

                return $name . $reference;
            },
        ],
        [
            'attribute' => 'customer_id',
            'format' => 'raw',
            'visible' => !$picker && !$isInCustomer,
            'content' => function ($model) use ($iconTypes) {
                /** @var Expense $model */

                if (!$model->customer_id) {
                    return '';
                }

                $type = Icon::show($iconTypes[$model->customer->type], [
                    'class' => 'icon icons8-size',
                    'title' => Yii::t('app', $model->customer->typeText),
                    'data-toggle' => 'tooltip',
                ]);
                $name = Html::a($type . Html::encode($model->customer->company_name), ['/crm/admin/customer/view', 'id' => $model->customer_id], [
                    'class' => 'd-block',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'customer-view-modal',
                ]);

                if ($model->customer->type === Customer::TYPE_COMPANY) {
                    $primaryContact = Html::tag('div', Html::encode($model->customer->primaryContact->name), ['class' => 'data-table-secondary-text']);
                    $name .= $primaryContact;
                }

                return $name;
            },
        ],
        [
            'class' => DateColumn::class,
            'type' => DateValidator::TYPE_DATE,
            'attribute' => 'date',
            'visible' => !$picker,
        ],
        [
            'attribute' => 'category_id',
            'content' => 'category.name',
            'visible' => !$picker,
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
        ],
        [
            'attribute' => 'is_billable',
            'visible' => !$picker,
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Expense $model */

                if ($model->is_billable) {
                    $icon = Icon::show('i8:add-shopping-cart', [
                        'class' => 'icon icons8-size mr-1 text-success',
                        'data-toggle' => 'tooltip',
                        'title' => Yii::t('app', 'Billable'),
                    ]);
                    $billable = Html::tag('div', $icon . Yii::t('app', 'Billable'));

                    if (empty($model->isBilled)) {
                        $label = Html::tag('div', Yii::t('app', 'Not Billed Yet'), [
                            'class' => 'text-danger data-table-secondary-text',
                        ]);
                    } else {
                        $invoiceLink = Html::a(Html::encode($model->invoiceItem->invoice->number), ['/finance/admin/invoice/view', 'id' => $model->invoiceItem->invoice_id], [
                            'data-lazy-container' => '#main-container',
                            'data-lazy-modal' => 'invoice-view-modal',
                        ]);
                        $label = Html::tag('div', Yii::t('app', 'Billed in: {invoice}', ['invoice' => $invoiceLink]), [
                            'class' => 'data-table-secondary-text',
                        ]);
                    }

                    return $billable . $label;
                }

                $icon = Icon::show('i8:clear-shopping-cart', [
                    'class' => 'icon icons8-size mr-1 text-danger',
                    'data-toggle' => 'tooltip',
                    'title' => Yii::t('app', 'Not Billable'),
                ]);
                $billable = Html::tag('div', $icon . Yii::t('app', 'Not Billable'));

                return $billable;
            },
        ],
        [
            'attribute' => 'currency_code',
            'visible' => !$picker,
            'format' => 'raw',
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'content' => function ($model) use ($baseCurrency) {
                /** @var Invoice $model */

                $code = Html::tag('div', Html::encode($model->currency_code));
                $rate = '';

                if ($model->currency_code !== $baseCurrency) {
                    $rate = Html::tag('div', Yii::$app->formatter->asDecimal(floatval($model->currency_rate)), [
                        'class' => 'data-table-secondary-text',
                    ]);
                }

                return $code . $rate;
            },
        ],
        [
            'attribute' => 'total',
            'format' => 'raw',
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_RIGHT,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_RIGHT,
            ],
            'content' => function ($model) use ($baseCurrency) {
                /** @var Expense $model */

                $value = Html::tag('div', Yii::$app->formatter->asCurrency($model->total, $model->currency_code), [
                    'class' => 'data-table-primary-text',
                ]);

                if ($model->currency_code !== $baseCurrency) {
                    $realValue = Html::tag('div', Yii::$app->formatter->asCurrency($model->real_total), [
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
            'visible' => !$picker,
            'controller' => '/finance/admin/expense',
            'buttons' => [
                'view' => [
                    'value' => [
                        'icon' => 'i8:eye',
                        'name' => Yii::t('app', 'View'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'expense-view-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'update' => [
                    'value' => [
                        'icon' => 'i8:edit',
                        'name' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'expense-form-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'add-to-invoice' => function($url,$model){
                    /** @var Expense $model */

                    if($model->is_billable && empty($model->invoice_item_id)) {
                        return Html::a(Icon::show('i8:transaction-2'), $url, [
                            'title' => Yii::t('app', 'Add to Invoice'),
                            'data-lazy-container' => '#main-container',
                            'data-lazy-modal' => 'add-to-invoice-form-modal',
                            'data-lazy-modal-size' => 'modal-md',
                            'data-toggle' => 'tooltip',
                        ]);
                    }
                }
            ],
        ],
    ],
], $dataTableOptions));

if (!$picker) {
    $dataTable->table->footer->addRow([
        'cells' => [
            'name' => [
                'format' => 'raw',
                'content' => Html::tag('div', Yii::t('app', 'Total'), [
                    'class' => 'data-table-primary-text font-size-lg',
                ]),
            ],
            'total' => [
                'format' => 'raw',
                'content' => Html::tag('div', Yii::$app->formatter->asCurrency($searchModel->totalValue), [
                    'class' => 'data-table-primary-text font-size-lg',
                ]),
                'hAlign' => Cell::H_ALIGN_RIGHT,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
        ],
    ]);
}

echo $this->block('@data-table');

DataTable::end();

echo $this->block('@end', $dataTable);
