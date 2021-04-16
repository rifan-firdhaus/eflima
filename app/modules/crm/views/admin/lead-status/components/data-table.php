<?php

use modules\account\web\admin\View;
use modules\crm\models\LeadStatus;
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
    'id' => 'lead-status-data-table',
    'card' => false,
    'linkPager' => false,
    'idAttribute' => 'id',
    'lazy' => false,
    'columns' => [
        [
            'class' => CheckboxColumn::class,
        ],
        [
            'attribute' => 'label',
            'contentCell' => [
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'format' => 'raw',
            'content' => function ($model) {
                /** @var LeadStatus $model */

                $colorname = Html::tag('span', '', [
                    'class' => 'color-description',
                    'style' => "background-color:{$model->color_label}",
                ]);
                $name = Html::tag('span', Html::encode($model->label), [
                    'style' => "color: {$model->color_label}",
                ]);

                return Html::a($colorname . $name, ['/crm/admin/lead-status/update', 'id' => $model->id], [
                    'data-lazy-modal' => 'lead-status-form-modal',
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
                    'data-lazy-options' => ['method' => 'POST']
                ],
            ],
            'falseLabel' => Yii::t('app', 'Disabled'),
            'falseActionLabel' => Icon::show('i8:unavailable', ['class' => 'icons8-size mr-2']) . Yii::t('app', 'Disable'),
            'falseItemOptions' => [
                'linkOptions' => [
                    'class' => 'text-danger',
                    'data-lazy-options' => ['method' => 'POST']
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
                /** LeadStatus $model */

                return ['/crm/admin/lead-status/enable', 'id' => $model->id, 'enable' => $value];
            },
        ],
        [
            'attribute' => 'updated_at',
            'class' => DateColumn::class,
        ],
        [
            'class' => ActionColumn::class,
            'sort' => 1000000,
            'controller' => '/crm/admin/lead-status',
            'buttons' => [
                'view' => false,
                'delete' => [
                    'visible' => Yii::$app->user->can('admin.setting.crm.lead-status.delete'),
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
                'update' => [
                    'visible' => Yii::$app->user->can('admin.setting.crm.lead-status.update'),
                    'value' => [
                        'icon' => 'i8:edit',
                        'name' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal-size' => 'modal-md',
                        'data-lazy-modal' => 'lead-status-form-modal',
                    ],
                ],
            ],
        ],
    ],
], $dataTableOptions));

echo $this->block('@data-table');

DataTable::end();

echo $this->block('@end', $dataTable);
