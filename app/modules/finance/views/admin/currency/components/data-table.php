<?php

use modules\account\web\admin\View;
use modules\finance\models\Currency;
use modules\ui\widgets\data_table\columns\ActionColumn;
use modules\ui\widgets\data_table\columns\BooleanColumn;
use modules\ui\widgets\data_table\columns\CheckboxColumn;
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
    'id' => 'currency-data-table',
    'card' => false,
    'linkPager' => false,
    'idAttribute' => 'code',
    'lazy' => false,
    'columns' => [
        [
            'class' => CheckboxColumn::class,
        ],
        [
            'attribute' => 'name',
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Currency $model */

                return Html::a(Html::encode($model->name), ['/finance/admin/currency/update', 'code' => $model->code], [
                    'data-lazy-modal' => 'currency-form-modal',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                    'data-lazy-modal-size' => 'modal-md',
                    'class' => 'd-block data-table-primary-text',
                ]);
            },
        ],
        'code',
        'symbol',
        [
            'attribute' => 'example',
            'content' => function ($model) {
                return Yii::$app->formatter->asCurrency(1200000, $model->code);
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
            'trueActionLabel' => Icon::show('i8:ok', ['class' => 'icons8-size mr-2']) . Yii::t('app', 'Enable'),
            'trueItemOptions' => [
                'linkOptions' => [
                    'data-lazy-options'=> ['method' => 'POST'],
                ],
            ],
            'falseLabel' => Yii::t('app', 'Disabled'),
            'falseActionLabel' => Icon::show('i8:unavailable', ['class' => 'icons8-size mr-2']) . Yii::t('app', 'Disable'),
            'falseItemOptions' => [
                'linkOptions' => [
                    'class' => 'text-danger',
                    'data-lazy-options'=> ['method' => 'POST'],
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
                /** Currency $model */

                return ['/finance/admin/currency/enable', 'code' => $model->code, 'enable' => $value];
            },
        ],
        [
            'class' => ActionColumn::class,
            'sort' => 1000000,
            'controller' => '/finance/admin/currency',
            'buttons' => [
                'view' => false,
                'update' => [
                    'visible' => Yii::$app->user->can('admin.setting.finance.currency.update'),
                    'value' => [
                        'icon' => 'i8:edit',
                        'name' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal-size' => 'modal-md',
                        'data-lazy-modal' => 'currency-form-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'delete' => [
                    'visible' => Yii::$app->user->can('admin.setting.finance.currency.delete'),
                    'value' => [
                        'icon' => 'i8:trash',
                        'label' => Yii::t('app', 'Delete'),
                        'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
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

echo $this->block('@data-table');

DataTable::end();

echo $this->block('@end', $dataTable);
