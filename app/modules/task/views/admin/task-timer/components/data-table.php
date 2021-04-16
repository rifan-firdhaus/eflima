<?php

use modules\account\web\admin\View;
use modules\task\models\TaskTimer;
use modules\ui\widgets\data_table\columns\ActionColumn;
use modules\ui\widgets\data_table\columns\CheckboxColumn;
use modules\ui\widgets\data_table\DataTable;
use modules\ui\widgets\Icon;
use modules\ui\widgets\table\cells\Cell;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var View               $this
 * @var ActiveDataProvider $dataProvider
 * @var array              $dataTableOptions
 * @var array              $params
 */

if (!isset($dataTableOptions)) {
    $dataTableOptions = [];
}

echo $this->block('@begin', [
    'dataTableOptions' => &$dataTableOptions,
]);

$dataTable = DataTable::begin(ArrayHelper::merge([
    'dataProvider' => $dataProvider,
    'id' => 'task-timer-data-table',
    'card' => false,
    'linkPager' => false,
    'idAttribute' => 'id',
    'lazy' => false,
    'columns' => [
        [
            'class' => CheckboxColumn::class,
        ],
        [
            'attribute' => 'task.title',
            'label' => Yii::t('app', 'Task'),
            'format' => 'raw',
            'visible' => !isset($params['task_id']),
            'contentCell' => [
                'width' => '250px',
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'content' => function ($model) {
                return Html::a(Html::encode($model->task->title), ['/task/admin/task/view', 'id' => $model->task_id], [
                    'class' => 'data-table-primary-text',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'task-view',
                ]);
            },
        ],
        [
            'attribute' => 'started_at',
            'label' => Yii::t('app', 'Start'),
            'format' => 'raw',
            'content' => function ($model) {
                /** @var TaskTimer $model */

                $date = Html::tag('div', Yii::$app->formatter->asDatetime($model->started_at), ['class' => 'text-nowrap']);
                $by = Html::tag('div', Yii::t('app', 'by: {name}', [
                    'name' => Html::a(
                        Html::encode($model->starter->name),
                        ['/account/admin/staff/profile', 'id' => $model->starter_id]
                    ),
                ]), ['class' => 'data-table-secondary-text']);

                return $date . $by;
            },
        ],
        [
            'attribute' => 'stopped_at',
            'label' => Yii::t('app', 'Stop'),
            'format' => 'raw',
            'content' => function ($model) {
                /** @var TaskTimer $model */

                if (!$model->stopper_id) {
                    return Html::tag('span', Icon::show('i8:play', ['class' => 'icons8-size icon mr-1']) . Yii::t('app', 'Running'), [
                        'class' => 'badge badge-clean badge-warning p-2 text-uppercase',
                    ]);
                }

                $date = Html::tag('div', Yii::$app->formatter->asDatetime($model->stopped_at), ['class' => 'text-nowrap']);
                $by = Html::tag('div', Yii::t('app', 'by: {name}', [
                    'name' => Html::a(
                        Html::encode($model->stopper->name),
                        ['/account/admin/staff/profile', 'id' => $model->stopper_id]
                    ),
                ]), ['class' => 'data-table-secondary-text']);

                return $date . $by;
            },
        ],
        [
            'attribute' => 'duration',
            'format' => 'raw',
            'content' => function ($model) {
                if (!$model->stopped_at) {
                    return;
                }

                $duration = $model->stopped_at - $model->started_at;

                return Html::tag('span', Yii::$app->formatter->asShortDuration($duration), [
                    'data-toggle' => 'tooltip',
                    'title' => Yii::$app->formatter->asDuration($duration),
                    'class' => 'h5',
                ]);
            },
        ],
        [
            'class' => ActionColumn::class,
            'controller' => '/task/admin/task-timer',
            'sort' => 1000000,
            'buttons' => [
                'view' => false,
                'update' => [
                    'visible' => Yii::$app->user->can('admin.task.timer.update'),
                    'value' => [
                        'icon' => 'i8:edit',
                        'label' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal-size' => 'modal-md',
                        'data-lazy-modal' => 'task-timer-form-modal',
                    ],
                ],
                'delete' => [
                    'visible' => Yii::$app->user->can('admin.task.timer.delete'),
                    'value' => [
                        'icon' => 'i8:trash',
                        'label' => Yii::t('app', 'Delete'),
                        'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                            'object_name' => Yii::t('app', 'this item'),
                        ]),
                        'class' => 'text-danger',
                        'data-lazy-container' => '#main#',
                        'data-lazy-options' => ['scroll' => false, 'method' => 'POST'],
                    ],
                ],
            ],
            'urlCreator' => function ($action, $model) {
                return Url::to(["/task/admin/task-timer/{$action}", 'id' => $model->id]);
            },
        ],
    ],
], $dataTableOptions));

echo $this->block('@data-table', compact('dataTable'));

DataTable::end();

echo $this->block('@end', compact('dataTable'));
