<?php

use modules\account\web\admin\View;
use modules\calendar\models\Event;
use modules\calendar\models\forms\event\EventSearch;
use modules\ui\widgets\data_table\columns\ActionColumn;
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
 * @var EventSearch        $searchModel
 */

if (!isset($dataTableOptions)) {
    $dataTableOptions = [];
}

$isRelatedView = !empty($searchModel->params['model']) && empty($searchModel->params['models']);

echo $this->block('@begin', [
    'dataTableOptions' => &$dataTableOptions,
]);

$dataTable = DataTable::begin(ArrayHelper::merge([
    'dataProvider' => $dataProvider,
    'id' => 'event-data-table',
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
                /** @var Event $model */
                return Html::a(Html::encode($model->name), ['/calendar/admin/event/view', 'id' => $model->id], [
                    'data-lazy-modal' => 'event-form',
                    'data-lazy-container' => '#main-container',
                    'class' => 'd-block data-table-primary-text',
                    'data-lazy-modal-size' => 'modal-lg',
                ]);
            },
        ],
        [
            'attribute' => 'model',
            'visible' => !$isRelatedView,
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Event $model */

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
            'content' => function ($model) {
                /** @var Event $model */

                $indicator = '';
                $isStarted = $model->isStarted;

                if ($isStarted) {
                    $indicator = Icon::show('i8:flash-on', ['class' => 'icons8-size mr-1 icon']);
                }

                $date = Html::tag('div', $indicator . Yii::$app->formatter->asDatetime($model->start_date), [
                    'class' => ($isStarted ? 'text-primary important' : ''),
                ]);
                $relativeTime = Html::tag(
                    'div',
                    Yii::$app->formatter->asRelativeTime($model->start_date),
                    ['class' => 'data-table-secondary-text ' . ($isStarted ? 'text-primary' : '')]
                );

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
            'attribute' => 'end_date',
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Event $model */

                $indicator = '';
                $isStarted = $model->isStarted;

                if ($isStarted) {
                    $indicator = Icon::show('i8:flash-on', ['class' => 'icons8-size mr-1 icon']);
                }

                $date = Html::tag('div', $indicator . Yii::$app->formatter->asDatetime($model->end_date), [
                    'class' => ($isStarted ? 'text-primary important' : ''),
                ]);
                $relativeTime = Html::tag(
                    'div',
                    Yii::$app->formatter->asRelativeTime($model->end_date),
                    ['class' => 'data-table-secondary-text ' . ($isStarted ? 'text-primary' : '')]
                );

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
            'format' => 'raw',
            'attribute' => 'member_ids',
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'content' => function ($model) {
                /** @var Event $model */

                $members = $model->members;
                $result = [];
                $more = count($members) - 5;

                foreach ($members AS $index => $member) {
                    $result[] = Html::tag('div', Html::img($member->account->getFileVersionUrl('avatar', 'thumbnail')), [
                        'class' => 'avatar-list-item',
                        'data-toggle' => 'tooltip',
                        'title' => $member->name,
                    ]);

                    if ($index === 1 && $more > 0) {
                        $result[] = Html::tag('div', "+{$more}", [
                            'class' => 'avatar-list-item-more',
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
            'controller' => '/calendar/admin/event',
            'buttons' => [
                'view' => [
                    'visible' => Yii::$app->user->can('admin.event.view'),
                    'value' => [
                        'icon' => 'i8:eye',
                        'data-toggle' => 'tooltip',
                        'label' => Yii::t('app', 'View'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'event-view-modal',
                        'data-lazy-modal-size' => 'modal-lg',
                    ],
                ],
                'update' => [
                    'visible' => Yii::$app->user->can('admin.event.update'),
                    'value' => [
                        'icon' => 'i8:edit',
                        'data-toggle' => 'tooltip',
                        'label' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'event-form-modal',
                        'data-lazy-modal-size' => 'modal-lg',
                    ],
                ],
                'delete' => [
                    'visible' => Yii::$app->user->can('admin.event.delete'),
                    'value' => [
                        'icon' => 'i8:trash',
                        'label' => Yii::t('app', 'Delete'),
                        'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                            'object_name' => Yii::t('app', 'this item'),
                        ]),
                        'class' => 'text-danger',
                        'data-lazy-container' => '#main#',
                        'data-lazy-options' => ['scroll' => false,'method' => 'DELETE'],
                    ],
                ]
            ],
        ],
    ],
], $dataTableOptions));

echo $this->block('@data-table');

DataTable::end();

echo $this->block('@end', $dataTable);
