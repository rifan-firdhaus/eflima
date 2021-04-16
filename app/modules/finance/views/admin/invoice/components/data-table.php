<?php

use modules\account\web\admin\View;
use modules\core\validators\DateValidator;
use modules\crm\models\Customer;
use modules\finance\models\Expense;
use modules\finance\models\forms\invoice\InvoiceSearch;
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
 * @var InvoiceSearch      $searchModel
 */
$baseCurrency = Yii::$app->setting->get('finance/base_currency');

if (!isset($dataTableOptions)) {
    $dataTableOptions = [];
}

$isInCustomer = !empty($searchModel->params['customer_id']);

echo $this->block('@begin', [
    'dataTableOptions' => &$dataTableOptions,
]);

$iconTypes = [
    Customer::TYPE_PERSONAL => 'i8:contacts',
    Customer::TYPE_COMPANY => 'i8:business-building',
];

$dataTable = DataTable::begin(ArrayHelper::merge([
    'dataProvider' => $dataProvider,
    'id' => 'invoice-data-table',
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
                /** @var Invoice $model */

                $name = Html::a(Html::encode($model->number), ['/finance/admin/invoice/view', 'id' => $model->id], [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                    'data-lazy-modal' => 'invoice-view-modal',
                    'class' => 'd-block text-monospace data-table-primary-text',
                ]);

                return $name;
            },
        ],
        [
            'class' => DateColumn::class,
            'type' => DateValidator::TYPE_DATE,
            'attribute' => 'date',
        ],
        [
            'attribute' => 'due_date',
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Invoice $model */

                $isDue = $model->isPastDue;
                $date = Html::tag('div', Yii::$app->formatter->asDate($model->due_date));
                $relative = Html::tag('div', Yii::$app->formatter->asRelativeTime($model->due_date), [
                    'class' => 'data-table-secondary-text ',
                ]);

                return Html::tag('div', $date . $relative, [
                    'class' => ($isDue ? 'text-danger font-weight-bold' : '') . ' d-inline-block',
                    'data-toggle' => 'tooltip',
                    'title' => ($isDue ? Yii::t('app', 'Overdue') : ''),
                ]);
            },
        ],
        [
            'attribute' => 'customer_id',
            'format' => 'raw',
            'visible' => !$isInCustomer,
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
            'attribute' => 'currency_code',
            'format' => 'raw',
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_RIGHT,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_RIGHT,
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
            'attribute' => 'grand_total',
            'format' => 'raw',
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_RIGHT,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_RIGHT,
            ],
            'content' => function ($model) use ($baseCurrency) {
                /** @var Invoice $model */

                $value = Html::tag('div', Yii::$app->formatter->asCurrency($model->grand_total, $model->currency_code), [
                    'class' => 'data-table-primary-text',
                ]);

                if ($model->currency_code !== $baseCurrency) {
                    $realValue = Html::tag('div', Yii::$app->formatter->asCurrency($model->real_grand_total, $baseCurrency), [
                        'class' => 'data-table-secondary-text',
                    ]);

                    return $value . $realValue;
                }

                return $value;
            },
        ],
        [
            'attribute' => 'total_due',
            'format' => 'raw',
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_RIGHT,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_RIGHT,
            ],
            'content' => function ($model) use ($baseCurrency) {
                /** @var Invoice $model */

                $value = Html::tag('div', Yii::$app->formatter->asCurrency($model->total_due, $model->currency_code), [
                    'class' => 'data-table-primary-text',
                ]);

                if ($model->currency_code !== $baseCurrency) {
                    $realValue = Html::tag('div', Yii::$app->formatter->asCurrency($model->real_total_due, $baseCurrency), [
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
            'controller' => '/finance/admin/invoice',
            'buttons' => [
                'update' => [
                    'visible' => Yii::$app->user->can('admin.invoice.update'),
                    'value' => [
                        'icon' => 'i8:edit',
                        'name' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'invoice-form-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'view' => [
                    'visible' => Yii::$app->user->can('admin.invoice.view.detail'),
                    'value' => [
                        'icon' => 'i8:eye',
                        'name' => Yii::t('app', 'View'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'invoice-view-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'download' => [
                    'visible' => Yii::$app->user->can('admin.invoice.view.detail'),
                    'value' => [
                        'icon' => 'i8:download',
                        'name' => Yii::t('app', 'Download'),
                        'data-lazy' => 0
                    ],
                ],
                'delete' => [
                    'visible' => Yii::$app->user->can('admin.invoice.delete'),
                    'value' => [
                        'icon' => 'i8:trash',
                        'label' => Yii::t('app', 'Delete'),
                        'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                            'object_name' => Yii::t('app', 'this item'),
                        ]),
                        'class' => 'text-danger',
                        'data-lazy-container' => '#main#',
                        'data-lazy-options' => ['scroll' => false, 'method' => 'DELETE'],
                    ],
                ],
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
        'grand_total' => [
            'format' => 'raw',
            'content' => Html::tag('div', Yii::$app->formatter->asCurrency($searchModel->sumOfGrandTotal), [
                'class' => 'data-table-primary-text font-size-lg',
            ]),
            'hAlign' => Cell::H_ALIGN_RIGHT,
            'vAlign' => Cell::V_ALIGN_CENTER,
        ],
        'total_due' => [
            'format' => 'raw',
            'content' => Html::tag('div', Yii::$app->formatter->asCurrency($searchModel->sumOfTotalDue), [
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
