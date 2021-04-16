<?php

use modules\account\web\admin\View;
use modules\address\assets\FlagIconAsset;
use modules\crm\models\Customer;
use modules\ui\widgets\data_table\columns\ActionColumn;
use modules\ui\widgets\data_table\columns\CheckboxColumn;
use modules\ui\widgets\data_table\DataTable;
use modules\ui\widgets\Icon;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

FlagIconAsset::register($this);

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

$iconTypes = [
    Customer::TYPE_PERSONAL => 'i8:contacts',
    Customer::TYPE_COMPANY => 'i8:business-building',
];

$dataTable = DataTable::begin(ArrayHelper::merge([
    'dataProvider' => $dataProvider,
    'id' => 'customer-data-table',
    'card' => false,
    'linkPager' => false,
    'idAttribute' => 'id',
    'lazy' => false,
    'columns' => [
        [
            'class' => CheckboxColumn::class,
        ],
        [
            'attribute' => 'company_name',
            'label' => Yii::t('app', 'Name'),
            'format' => 'raw',
            'content' => function ($model) use ($iconTypes) {
                /** @var Customer $model */

                $type = Icon::show($iconTypes[$model->type], [
                    'class' => 'icon icons8-size',
                    'title' => Yii::t('app', $model->typeText),
                    'data-toggle' => 'tooltip',
                ]);
                $name = Html::a($type . Html::encode($model->company_name), ['/crm/admin/customer/view', 'id' => $model->id], [
                    'class' => 'd-block',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'customer-view-modal',
                ]);

                if ($model->type === Customer::TYPE_COMPANY) {
                    $primaryContact = Html::tag('div', Html::encode($model->primaryContact->name), ['class' => 'data-table-secondary-text']);
                    $name .= $primaryContact;
                }

                return $name;
            },
        ],
        [
            'attribute' => 'contact',
            'format' => 'raw',
            'content' => function ($model) {
                /** @var Customer $model */

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
                /** @var Customer $model */

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
            'class' => ActionColumn::class,
            'sort' => 1000000,
            'controller' => '/crm/admin/customer',
            'buttons' => [
                'view' => [
                    'visible' => Yii::$app->user->can('admin.customer.view.detail'),
                    'value' => [
                        'icon' => 'i8:eye',
                        'name' => Yii::t('app', 'View'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'customer-view-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'update' => [
                    'visible' => Yii::$app->user->can('admin.customer.update'),
                    'value' => [
                        'icon' => 'i8:edit',
                        'name' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'customer-form-modal',
                        'data-toggle' => 'tooltip',
                    ],
                ],
                'delete' => [
                    'visible' => Yii::$app->user->can('admin.customer.delete'),
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
