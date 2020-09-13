<?php

use modules\account\web\admin\View;
use modules\content\models\Post;
use yii\helpers\Html;
use modules\ui\widgets\data_table\columns\ActionColumn;
use modules\ui\widgets\data_table\columns\BooleanColumn;
use modules\ui\widgets\data_table\columns\CheckboxColumn;
use modules\ui\widgets\data_table\DataTable;
use modules\ui\widgets\Icon;
use modules\ui\widgets\table\cells\Cell;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

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
    'id' => 'post-data-table',
    'card' => false,
    'linkPager' => false,
    'idAttribute' => 'id',
    'lazy' => false,
    'columns' => [
        [
            'class' => CheckboxColumn::class,
        ],
        [
            'attribute' => 'picture',
            'format' => 'raw',
            'label' => '',
            'contentCell' => [
                'width' => '55px',
                'vAlign' => Cell::V_ALIGN_CENTER,
                'options' => [
                    'class' => 'pr-0',
                ],
            ],
            'content' => function ($model) {
                /**
                 * @var Post $model
                 */

                return Html::img($model->getFileVersionUrl('picture', 'thumbnail', Yii::getAlias('@web/public/img/avatar.png')), [
                    'class' => 'w-100 rounded',
                ]);
            },
        ],
        [
            'attribute' => 'title',
            'format' => 'raw',
            'content' => function ($model) {
                $title = Html::a(Html::encode($model->title), ['/content/admin/post/update', 'id' => $model->id], [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'post-form-modal',
                    'class' => 'data-table-primary-text',
                ]);
                $date = Html::tag('div', Yii::$app->formatter->asDatetime($model->created_at), [
                    'class' => 'data-table-secondary-text',
                ]);

                return $title . $date;
            },
        ],
        [
            'attribute' => 'is_published',
            'class' => BooleanColumn::class,
            'contentCell' => [
                'vAlign' => Cell::V_ALIGN_CENTER,
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'trueLabel' => Yii::t('app', 'Published'),
            'falseLabel' => Yii::t('app', 'Not Published'),
            'trueActionLabel' => Icon::show('i8:ok', ['class' => 'icons8-size mr-2']) . Yii::t('app', 'Publish'),
            'falseActionLabel' => Icon::show('i8:unavailable', ['class' => 'icons8-size mr-2']) . Yii::t('app', 'Unpublish'),
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
                /** @var Post $model */

                return ['/content/admin/post/publish', 'id' => $model->id, 'publish' => $value];
            },
        ],
        [
            'class' => ActionColumn::class,
            'buttons' => [
                'view' => false,
                'update' => [
                    'value' => [
                        'icon' => 'i8:edit',
                        'label' => Yii::t('app', 'Update'),
                        'data-toggle' => 'tooltip',
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'post-form-modal',
                    ],
                ],
            ],
        ],
    ],
], $dataTableOptions));

echo $this->block('@data-table');

DataTable::end();

echo $this->block('@end', $dataTable);