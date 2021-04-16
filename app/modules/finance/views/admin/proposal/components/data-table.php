<?php

use modules\account\web\admin\View;
use modules\address\assets\FlagIconAsset;
use modules\core\validators\DateValidator;
use modules\finance\models\Proposal;
use modules\finance\widgets\inputs\ProposalStatusDropdown;
use modules\task\models\Task;
use modules\ui\widgets\data_table\columns\ActionColumn;
use modules\ui\widgets\data_table\columns\CheckboxColumn;
use modules\ui\widgets\data_table\columns\DateColumn;
use modules\ui\widgets\data_table\DataTable;
use modules\ui\widgets\table\cells\Cell;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View               $this
 * @var ActiveDataProvider $dataProvider
 * @var array              $dataTableOptions
 * @var array              $params
 */

FlagIconAsset::register($this);

$isRelatedView = !empty($params['model']) && empty($params['models']);

if (!isset($dataTableOptions)) {
    $dataTableOptions = [];
}

echo $this->block('@begin', [
    'dataTableOptions' => &$dataTableOptions,
]);

$dataTable = DataTable::begin(ArrayHelper::merge([
    'dataProvider' => $dataProvider,
    'id' => 'proposal-data-table',
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
            'label' => Yii::t('app', 'Title'),
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Proposal $model */

                $name = Html::a(Html::encode($model->title), ['/finance/admin/proposal/view', 'id' => $model->id], [
                    'class' => 'd-block',
                    'data-lazy-modal' => 'proposal-view-modal',
                    'data-lazy-container' => '#main-container',
                ]);
                $number = Html::tag('div', $model->number);

                return $name . $number;
            },
        ],
        [
            'attribute' => 'model',
            'visible' => !$isRelatedView,
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Proposal $model */

                if (empty($model->model)) {
                    return;
                }

                $object = $model->getRelatedObject();

                $relatedRecordName = $object->getLink($model->getRelatedModel());

                if (is_null($relatedRecordName)) {
                    $relatedRecordName = $object->getName($model->getRelatedModel());
                }

                $relatedType = Html::tag('div', $object->getLabel(), [
                    'class' => 'data-table-secondary-text text-uppercase',
                ]);

                return $relatedRecordName . $relatedType;
            },
        ],
        [
            'class' => DateColumn::class,
            'type' => DateValidator::TYPE_DATE,
            'attribute' => 'date',
        ],
        [
            'attribute' => 'status_id',
            'format' => 'raw',
            'content' => function ($model) {
                return ProposalStatusDropdown::widget([
                    'value' => $model->status_id,
                    'url' => function ($status) use ($model) {
                        return ['/finance/admin/proposal/change-status', 'id' => $model->id, 'status' => $status['id']];
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
                /** @var Proposal $model */

                $assignees = $model->assignees;
                $result = [];
                $more = count($assignees) - 2;

                foreach ($assignees AS $index => $assignee) {
                    $result[] = Html::tag('div', Html::img($assignee->account->getFileVersionUrl('avatar', 'thumbnail')), [
                        'class' => 'avatar-list-item',
                        'data-toggle' => 'tooltip',
                        'title' => $assignee->name,
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
            'controller' => '/finance/admin/proposal',
            'buttons' => [
                'view' => [
                    'visible' => Yii::$app->user->can('admin.proposal.view.detail'),
                    'value' => [
                        'icon' => 'i8:eye',
                        'name' => Yii::t('app', 'View'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'proposal-view-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'update' => [
                    'visible' => Yii::$app->user->can('admin.proposal.update'),
                    'value' => [
                        'icon' => 'i8:edit',
                        'name' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'proposal-form-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'delete' => [
                    'visible' => Yii::$app->user->can('admin.proposal.delete'),
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

echo $this->block('@data-table');

DataTable::end();

echo $this->block('@end', $dataTable);
