<?php

use modules\account\web\admin\View;
use modules\finance\models\ExpenseCategory;
use modules\ui\widgets\data_table\columns\ActionColumn;
use modules\ui\widgets\data_table\columns\BooleanColumn;
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
 */

if (!isset($dataTableOptions)) {
    $dataTableOptions = [];
}

echo $this->block('@begin', [
    'dataTableOptions' => &$dataTableOptions,
]);

$dataTable = DataTable::begin(ArrayHelper::merge([
    'dataProvider' => $dataProvider,
    'id' => 'expense-category-data-table',
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
            'content' => function ($model) {
                /** @var ExpenseCategory $model */

                return Html::a(Html::encode($model->name), ['/finance/admin/expense-category/update', 'id' => $model->id], [
                    'data-lazy-modal' => 'expense-category-form',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                    'data-lazy-modal-size' => 'modal-md',
                    'class' => 'd-block data-table-primary-text',
                ]);
            },
        ],
        [
            'attribute' => 'description',
            'contentCell' => [
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
        ],
        [
            'attribute' => 'total_expense',
            'label' => Yii::t('app', 'Expense'),
            'format' => 'raw',
            'content' => function ($model) {
                /** @var ExpenseCategory $model */

                $amountOfExpenses = $model->getExpenses()->count();
                $totalExpenses = $model->getExpenses()->sum('real_total');

                $amount = Html::a(Yii::t('app', '{amount} Expenses', ['amount' => Yii::$app->formatter->asDecimal($amountOfExpenses)]),
                    ['/finance/admin/expense/index', 'ExpenseSearch' => ['category_id' => $model->id]], [
                        'data-lazy-container' => '#main#',
                    ]);
                $total = Html::tag('div', Yii::$app->formatter->asCurrency($totalExpenses), [
                    'class' => 'data-table-secondary-text',
                ]);

                return $amount . $total;
            },
        ],
        [
            'attribute' => 'is_enabled',
            'class' => BooleanColumn::class,
            'contentCell' => [
                'vAlign' => Cell::V_ALIGN_CENTER,
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'trueLabel' => Yii::t('app', 'Enabled'),
            'falseLabel' => Yii::t('app', 'Disabled'),
            'trueActionLabel' => Icon::show('i8:ok', ['class' => 'icons8-size mr-2']) . Yii::t('app', 'Enable'),
            'falseActionLabel' => Icon::show('i8:unavailable', ['class' => 'icons8-size mr-2']) . Yii::t('app', 'Disable'),
            'falseItemOptions' => [
                'linkOptions' => [
                    'class' => 'text-danger',
                ],
            ],
            'buttonOptions' => function ($value) {
                return [
                    'buttonOptions' => [
                        'href' => '#',
                        'class' => ['widget' => 'badge badge-clean text-uppercase p-2 ' . (!$value ? 'badge-danger' : 'badge-primary')],
                    ],
                ];
            },
            'url' => function ($value, $model) {
                /** ExpenseCategory $model */

                return ['/finance/admin/expense-category/enable', 'id' => $model->id, 'enable' => $value];
            },
        ],
        [
            'attribute' => 'updated_at',
            'class' => DateColumn::class,
        ],
        [
            'class' => ActionColumn::class,
            'controller' => '/finance/admin/expense-category',
            'sort' => 1000000,
            'buttons' => [
                'view' => false,
                'update' => [
                    'value' => [
                        'icon' => 'i8:edit',
                        'label' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal-size' => 'modal-md',
                        'data-lazy-modal' => 'expense-category-form-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
            ],
        ],
    ],
], $dataTableOptions));

echo $this->block('@data-table');

DataTable::end();

echo $this->block('@end', $dataTable);