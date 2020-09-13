<?php

use modules\account\web\admin\View;
use modules\support\models\TicketPriority;
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
    'id' => 'ticket-priority-data-table',
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
                /** @var TicketPriority $model */

                $colorLabel = Html::tag('span', '', [
                    'class' => 'color-description',
                    'style' => "background-color:{$model->color_label}",
                ]);
                $label = Html::tag('span', Html::encode($model->label), [
                    'style' => "color: {$model->color_label}",
                ]);

                return Html::a($colorLabel . $label, ['/support/admin/ticket-priority/update', 'id' => $model->id], [
                    'data-lazy-modal' => 'ticket-priority-form',
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
                /** TicketPriority $model */

                return ['/support/admin/ticket-priority/enable', 'id' => $model->id, 'enable' => $value];
            },
        ],
        [
            'attribute' => 'updated_at',
            'class' => DateColumn::class,
        ],
        [
            'class' => ActionColumn::class,
            'controller' => '/support/admin/ticket-priority',
            'sort' => 1000000,
            'buttons' => [
                'view' => false,
                'update' => [
                    'value' => [
                        'icon' => 'i8:edit',
                        'label' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal-size' => 'modal-md',
                        'data-lazy-modal' => 'ticket-priority-form-modal',
                    ],
                ],
            ],
        ],
    ],
], $dataTableOptions));

echo $this->block('@data-table');

DataTable::end();

echo $this->block('@end', $dataTable);