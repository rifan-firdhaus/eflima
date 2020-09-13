<?php

use modules\account\web\admin\View;
use modules\address\assets\FlagIconAsset;
use modules\address\models\Country;
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

FlagIconAsset::register($this);

if (!isset($dataTableOptions)) {
    $dataTableOptions = [];
}

echo $this->block('@begin', [
    'dataTableOptions' => &$dataTableOptions,
]);

$dataTable = DataTable::begin(ArrayHelper::merge([
    'dataProvider' => $dataProvider,
    'id' => 'country-data-table',
    'card' => false,
    'linkPager' => false,
    'idAttribute' => 'code',
    'lazy' => false,
    'columns' => [
        [
            'class' => CheckboxColumn::class,
        ],
        [
            'attribute' => 'flag',
            'label' => '',
            'format' => 'raw',
            'contentCell' => [
                'width' => '30px',
                'options' => [
                    'class' => 'pr-0',
                ],
            ],
            'content' => function ($model) {
                /** @var Country $model */

                $code = strtolower($model->iso2);

                return "<span style=\"width: 30px;height: 23px;\" class=\"flag-icon  border flag-icon-{$code}\"></span>";
            },
        ],
        [
            'attribute' => 'name',
            'contentCell' => [
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Country $model */
                return Html::a(Html::encode($model->name), ['/address/admin/country/update', 'code' => $model->code], [
                    'data-lazy-modal' => 'country-form',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                    'data-lazy-modal-size' => 'modal-md',
                    'class' => 'd-block data-table-primary-text',
                ]);
            },
        ],
        [
            'attribute' => 'code',
            'label' => Yii::t('app', 'ISO3'),
        ],
        [
            'attribute' => 'iso2',
            'label' => Yii::t('app', 'ISO2'),
        ],
        [
            'attribute' => 'continent_code',
            'content' => 'continent',
        ],
        [
            'attribute' => 'phone_code',
        ],
        [
            'attribute' => 'currency_code',
            'label' => Yii::t('app', 'Currency'),
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
                /** @var Country $model */

                return ['/address/admin/country/enable', 'code' => $model->code, 'enable' => $value];
            },
        ],
        [
            'class' => ActionColumn::class,
            'sort' => 1000000,
            'controller' => '/address/admin/country',
            'buttons' => [
                'view' => false,
                'update' => [
                    'value' => [
                        'icon' => 'i8:edit',
                        'data-toggle' => 'tooltip',
                        'label' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal-size' => 'modal-md',
                        'data-lazy-modal' => 'country-form-modal',
                    ],
                ],
            ],
        ],
    ],
], $dataTableOptions));

echo $this->block('@data-table');

DataTable::end();

echo $this->block('@end', $dataTable);