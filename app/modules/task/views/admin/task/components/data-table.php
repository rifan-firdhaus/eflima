<?php

use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\task\models\Task;
use modules\task\models\TaskPriority;
use modules\task\models\TaskStatus;
use modules\task\widgets\inputs\TaskPriorityDropdown;
use modules\task\widgets\inputs\TaskStatusDropdown;
use modules\ui\widgets\data_table\columns\ActionColumn;
use modules\ui\widgets\data_table\columns\CheckboxColumn;
use modules\ui\widgets\data_table\DataTable;
use modules\ui\widgets\Icon;
use modules\ui\widgets\table\cells\Cell;
use yii\bootstrap4\Progress;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View               $this
 * @var array              $dataTableOptions
 * @var StaffAccount       $account
 * @var array              $params
 * @var ActiveDataProvider $dataProvider
 */
$time = time();
$account = Yii::$app->user->identity;
$isRelatedView = !empty($params['model']) && empty($params['models']);

if (!isset($dataTableOptions)) {
    $dataTableOptions = [];
}

echo $this->block('@begin', [
    'dataTableOptions' => &$dataTableOptions,
]);

$statuses = TaskStatus::find()->enabled()->createCommand()->queryAll();
$priorities = TaskPriority::find()->enabled()->createCommand()->queryAll();

$dataTable = DataTable::begin(ArrayHelper::merge([
    'dataProvider' => $dataProvider,
    'id' => 'task-data-table',
    'card' => false,
    'linkPager' => false,
    'idAttribute' => 'id',
    'lazy' => false,
    'columns' => [
        [
            'class' => CheckboxColumn::class,
        ],
        [
            'attribute' => 'title',
            'format' => 'raw',
            'contentCell' => [
                'width' => '230px',
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'content' => function ($model) use ($account) {
                /** @var Task $model */

                $isTimerStarted = $model->isTimerStarted($account->profile->id);
                $indicator = !$isTimerStarted ? "" : Html::tag('span', '', [
                    'class' => 'bubble-indicator mr-1 bg-danger animation-blink',
                    'title' => Yii::t('app', 'Recording'),
                    'data-toggle' => 'tooltip',
                ]);

                $title = Html::a($indicator . Html::encode($model->title), ['/task/admin/task/view', 'id' => $model->id], [
                    'class' => 'data-table-primary-text',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'task-view-modal',
                ]);

                $progressInPercent = $model->progress * 100;

                $progressBar = Progress::widget([
                    'percent' => $progressInPercent,
                    'options' => [
                        'class' => 'flex-grow-1',
                        'style' => 'height: 4px',
                    ],
                ]);
                $progressLabel = Html::tag('div', $progressInPercent . "%", ['class' => 'mr-2 text-primary data-table-primary-text']);
                $progress = Html::tag('div', $progressLabel . $progressBar, ['class' => 'd-flex mt-1 task-progress align-items-center', 'style' => 'opacity:0.9']);

                return $title . $progress;
            },
        ],
        [
            'attribute' => 'model',
            'visible' => !$isRelatedView,
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Task $model */

                if (empty($model->model)) {
                    return;
                }

                $object = $model->getRelatedObject();

                $relatedRecordName = $model->relatedObject->getLink($model->relatedModel);

                if (is_null($relatedRecordName)) {
                    $relatedRecordName = $model->relatedObject->getName($model->relatedModel);
                }

                $relatedType = Html::tag('div', $object->getLabel(), [
                    'class' => 'data-table-secondary-text text-uppercase',
                ]);

                return $relatedRecordName . $relatedType;
            },
        ],
        [
            'attribute' => 'started_date',
            'format' => 'raw',
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'content' => function ($model) {
                /** @var Task $model */

                $indicator = '';
                $isStarted = $model->isStarted;

                if ($isStarted) {
                    $indicator = Icon::show('i8:flash-on', [
                        'class' => 'icons8-size mr-1 icon',
                    ]);
                }

                $date = Html::tag('div', $indicator . Yii::$app->formatter->asDate($model->started_date), [
                    'class' => ($isStarted ? 'text-primary important' : ''),
                ]);
                $relativeTime = Html::tag('div', Yii::$app->formatter->asRelativeTime($model->started_date), [
                    'class' => 'data-table-secondary-text ' . ($isStarted ? 'text-primary' : ''),
                ]);

                if ($isStarted) {
                    return Html::tag('div', $date . $relativeTime, [
                        'data-toggle' => 'tooltip',
                        'title' => Yii::t('app', 'Started'),
                    ]);
                }

                return $date . $relativeTime;
            },
        ],
        [
            'attribute' => 'deadline_date',
            'format' => 'raw',
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'content' => function ($model) {
                /** @var Task $model */

                if (empty($model->deadline_date)) {
                    return;
                }

                $indicator = '';
                $isOverdue = $model->isOverdue;

                if ($isOverdue) {
                    $indicator = Icon::show('i8:error', [
                        'class' => 'icons8-size mr-1 icon text-danger animation-blink',
                    ]);
                }

                $date = Html::tag('div', $indicator . Yii::$app->formatter->asDate($model->deadline_date), [
                    'class' => ($isOverdue ? 'text-danger important' : ''),
                ]);
                $relativeTime = Html::tag('div', Yii::$app->formatter->asRelativeTime($model->deadline_date), [
                    'class' => 'data-table-secondary-text ' . ($isOverdue ? 'text-danger' : ''),
                ]);

                if ($isOverdue) {
                    return Html::tag('div', $date . $relativeTime, [
                        'data-toggle' => 'tooltip',
                        'title' => Yii::t('app', 'Overdue'),
                    ]);
                }

                return $date . $relativeTime;
            },
        ],
        [
            'attribute' => 'status_id',
            'format' => 'raw',
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'content' => function ($model) {
                return TaskStatusDropdown::widget([
                    'value' => $model->status_id,
                    'url' => function ($status) use ($model) {
                        return ['/task/admin/task/change-status', 'id' => $model->id, 'status' => $status['id']];
                    },
                ]);
            },
        ],
        [
            'attribute' => 'priority_id',
            'format' => 'raw',
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'content' => function ($model) {
                return TaskPriorityDropdown::widget([
                    'value' => $model->priority_id,
                    'url' => function ($priority) use ($model) {
                        return ['/task/admin/task/change-priority', 'id' => $model->id, 'priority' => $priority['id']];
                    },
                ]);
            },
        ],
        [
            'format' => 'raw',
            'attribute' => 'assignees',
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'content' => function ($model) {
                /** @var Task $model */

                $assignees = $model->assignees;
                $result = [];
                $more = count($assignees) - 2;

                foreach ($assignees AS $index => $assignee) {
                    $result[] = Html::tag('div', Html::img($assignee->account->getFileVersionUrl('avatar', 'thumbnail')), [
                        'class' => 'task-avatar',
                        'data-toggle' => 'tooltip',
                        'title' => $assignee->name,
                    ]);

                    if ($index === 1 && $more > 0) {
                        $result[] = Html::tag('div', "+{$more}", [
                            'class' => 'task-avatar-more',
                            'data-toggle' => 'tooltip',
                        ]);

                        break;
                    }
                }

                return implode('', $result);
            },
        ],
        [
            'class' => ActionColumn::class,
            'sort' => 1000000,
            'controller' => '/task/admin/task',
            'buttons' => [
                'start-timer' => [
                    'value' => function ($url, $model) use ($account) {
                        /** @var Task $model */

                        if (!$model->is_timer_enabled) {
                            return '';
                        }

                        $isTimerStarted = $model->isTimerStarted($account->profile->id);

                        if ($isTimerStarted) {
                            return [
                                'icon' => 'i8:stop',
                                'label' => Yii::t('app', 'Stop Timer'),
                                'url' => ['/task/admin/task/toggle-timer', 'id' => $model->id, 'start' => 0],
                                'data-confirmation' => Yii::t('app', 'You are about to stop the timer of this task, are you sure?'),
                                'data-toggle' => 'tooltip',
                                'data-lazy-options' => [
                                    'scroll' => false,
                                ],
                            ];
                        }

                        return [
                            'icon' => 'i8:play',
                            'label' => Yii::t('app', 'Start Timer'),
                            'url' => ['/task/admin/task/toggle-timer', 'id' => $model->id, 'start' => 1],
                            'data-confirmation' => Yii::t('app', 'You are about to start the timer of this task, are you sure?'),
                            'data-toggle' => 'tooltip',
                            'data-lazy-options' => [
                                'scroll' => false,
                            ],
                        ];
                    },
                ],
                'update' => [
                    'value' => [
                        'icon' => 'i8:edit',
                        'label' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'task-form-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'view' => [
                    'value' => [
                        'icon' => 'i8:eye',
                        'label' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'task-view-modal',
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