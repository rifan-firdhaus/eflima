<?php

use modules\account\web\admin\View;
use modules\crm\models\Customer;
use modules\support\models\Ticket;
use modules\support\widgets\inputs\TicketPriorityDropdown;
use modules\support\widgets\inputs\TicketStatusDropdown;
use modules\ui\widgets\data_table\columns\ActionColumn;
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
 * @var array              $params
 */

$isCustomerView = !empty($params['customer_id']);

if (!isset($dataTableOptions)) {
    $dataTableOptions = [];
}

echo $this->block('@begin', [
    'dataTableOptions' => &$dataTableOptions,
]);

$iconTypes = [
    Customer::TYPE_PERSONAL => 'i8:contacts',
    Customer::TYPE_COMPANY => 'i8:business-building',
];

$dataTable = DataTable::begin(ArrayHelper::merge([
    'dataProvider' => $dataProvider,
    'id' => 'ticket-data-table',
    'card' => false,
    'linkPager' => false,
    'idAttribute' => 'id',
    'lazy' => false,
    'columns' => [
        [
            'class' => CheckboxColumn::class,
        ],
        [
            'attribute' => 'subject',
            'contentCell' => [
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Ticket $model */

                $subject = Html::tag('span', Html::encode($model->subject));

                return Html::a($subject, ['/support/admin/ticket/view', 'id' => $model->id], [
                    'data-lazy-modal' => 'ticket-view',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-link' => true,
                    'class' => 'd-block data-table-primary-text',
                ]);
            },
        ],
        [
            'attribute' => 'contact_id',
            'visible' => !$isCustomerView,
            'format' => 'raw',
            'content' => function ($model) use ($iconTypes) {
                /** @var Ticket $model */

                $type = Icon::show($iconTypes[$model->contact->customer->type], [
                    'class' => 'icon icons8-size',
                    'title' => Yii::t('app', $model->contact->customer->typeText),
                    'data-toggle' => 'tooltip',
                ]);
                $name = Html::a($type . Html::encode($model->contact->customer->company_name), ['/crm/admin/customer/view', 'id' => $model->contact->customer_id], [
                    'class' => 'd-block',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'customer-view-model',
                ]);

                if ($model->contact->customer->type === Customer::TYPE_COMPANY) {
                    $primaryContact = Html::tag('div', Html::encode($model->contact->name), ['class' => 'data-table-secondary-text']);
                    $name .= $primaryContact;
                }

                return $name;
            },
        ],
        [
            'attribute' => 'department_id',
            'content' => 'department.name',
        ],
        [
            'attribute' => 'created_at',
            'class' => DateColumn::class,
        ],
        [
            'attribute' => 'status_id',
            'format' => 'raw',
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'content' => function ($model) {
                /** @var Ticket $model */

                return TicketStatusDropdown::widget([
                    'url' => ['/support/admin/ticket/change-status', 'id' => $model->id],
                    'value' => $model->status_id,
                ]);
            },
        ],
        [
            'attribute' => 'priority_id',
            'format' => 'raw',
            'contentCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
                'vAlign' => Cell::V_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'content' => function ($model) {
                /** @var Ticket $model */

                return TicketPriorityDropdown::widget([
                    'url' => ['/support/admin/ticket/change-priority', 'id' => $model->id],
                    'value' => $model->priority_id,
                ]);
            },
        ],
        [
            'class' => ActionColumn::class,
            'controller' => '/support/admin/ticket',
            'sort' => 1000000,
            'buttons' => [
                'view' => [
                    'value' => [
                        'icon' => 'i8:eye',
                        'label' => Yii::t('app', 'View'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'ticket-view',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'update' => [
                    'value' => [
                        'icon' => 'i8:edit',
                        'label' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal-size' => 'modal-lg',
                        'data-lazy-modal' => 'ticket-form-modal',
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