<?php

use modules\account\web\admin\View;
use modules\address\assets\FlagIconAsset;
use modules\crm\models\Lead;
use modules\crm\widgets\inputs\LeadStatusDropdown;
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
 * @var array              $params
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
    'id' => 'lead-data-table',
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
            'label' => Yii::t('app', 'Name'),
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Lead $model */

                $name = Html::a(Html::encode($model->name), ['/crm/admin/lead/view', 'id' => $model->id], [
                    'class' => 'd-block',
                    'data-lazy-modal' => 'lead-view-modal',
                    'data-lazy-container' => '#main-container',
                ]);

                return $name;
            },
        ],
        [
            'attribute' => 'contact',
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Lead $model */

                $email = Html::a(
                    Icon::show('i8:email', ['class' => 'mr-1 icons8-size']) . Html::encode($model->email),
                    'mailto:' . Html::encode($model->email),
                    ['class' => 'd-block']
                );
                $phone = '';

                if ($model->phone) {
                    $phone = Html::tag(
                        'div',
                        Icon::show('i8:phone', ['class' => 'mr-1 icons8-size']) . Html::encode($model->phone),
                        ['class' => 'data-table-secondary-text']
                    );
                }

                return $email . $phone;
            },
        ],
        [
            'attribute' => 'address',
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Lead $model */

                $flag = '';
                $detail = [];

                if ($model->province) {
                    $detail[] = Html::encode($model->province);
                }

                if ($model->country_code) {
                    $code = strtolower($model->country->iso2);
                    $flag = "<div style=\"width:30px;height: 25px\" class=\"flag-icon mr-2 border align-self-center flag-icon-{$code}\"></div>";
                    $detail[] = Html::encode($model->country->name);
                }

                $address = Html::encode($model->address);

                if ($model->city) {
                    $address .= ", " . Html::encode($model->city);
                }

                $address = Html::tag('div', $address);

                if ($detail) {
                    $address .= Html::tag('div', implode(', ', $detail), ['class' => 'data-table-secondary-text']);
                }

                return Html::tag('div', $flag . Html::tag('div', $address), ['class' => 'd-flex']);
            },
        ],
        [
            'attribute' => 'source_id',
            'content' => 'source.name',
        ],
        [
            'attribute' => 'status_id',
            'format' => 'raw',
            'content' => function ($model) {
                return LeadStatusDropdown::widget([
                    'value' => $model->status_id,
                    'url' => function ($status) use ($model) {
                        return ['/crm/admin/lead/change-status', 'id' => $model->id, 'status' => $status['id']];
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
                /** @var Lead $model */

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
            'controller' => '/crm/admin/lead',
            'buttons' => [
                'view' => [
                    'visible' => Yii::$app->user->can('admin.lead.view.detail'),
                    'value' => [
                        'icon' => 'i8:eye',
                        'name' => Yii::t('app', 'View'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'lead-view-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'update' => [
                    'visible' => Yii::$app->user->can('admin.lead.update'),
                    'value' => [
                        'icon' => 'i8:edit',
                        'name' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'lead-form-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'delete' => [
                    'visible' => Yii::$app->user->can('admin.lead.delete'),
                    'value' => [
                        'icon' => 'i8:trash',
                        'label' => Yii::t('app', 'Delete'),
                        'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
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
