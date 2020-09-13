<?php

use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\ui\widgets\data_table\columns\ActionColumn;
use modules\ui\widgets\data_table\columns\BooleanColumn;
use modules\ui\widgets\data_table\columns\CheckboxColumn;
use modules\ui\widgets\data_table\columns\DataColumn;
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
    'id' => 'staff-data-table',
    'card' => false,
    'linkPager' => false,
    'idAttribute' => 'id',
    'lazy' => false,
    'columns' => [
        [
            'class' => CheckboxColumn::class,
        ],
        [
            'attribute' => 'avatar',
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
                 * @var Staff $model
                 */

                return Html::img($model->account->getFileVersionUrl('avatar', 'thumbnail', Yii::getAlias('@web/public/img/avatar.png')), [
                    'class' => 'w-100 rounded-circle',
                ]);
            },
        ],
        [
            'attribute' => 'name',
            'class' => DataColumn::class,
            'format' => 'raw',
            'content' => function ($model) {
                /**
                 * @var Staff $model
                 */

                $name = Html::a([
                    'label' => Html::encode($model->account->username),
                    'url' => ['/account/admin/staff/update', 'id' => $model->id],
                    'class' => 'data-table-main-text',
                    'data-lazy-modal' => 'staff-form-modal',
                    'data-lazy-container' => '#main-container',
                ]);

                $username = Html::tag(
                    'div',
                    Html::encode($model->name),
                    ['class' => 'data-table-secondary-text']
                );

                return $name . $username;
            },
        ],
        [
            'attribute' => 'contact',
            'class' => DataColumn::class,
            'format' => 'raw',
            'content' => function ($model) {
                /**
                 * @var Staff $model
                 */

                $email = Html::a(
                    Icon::show('i8:email', ['class' => 'mr-1 icons8-size']) . Html::encode($model->account->email),
                    'mailto:' . Html::encode($model->account->email),
                    ['class' => 'd-block', 'data-lazy' => 0]
                );
                $phone = '';

                if ($model->account->contact->phone) {
                    $phone = Html::a(
                        Icon::show('i8:phone', ['class' => 'mr-1 icons8-size']) . Html::encode($model->account->contact->phone),
                        'tel:' . Html::encode($model->account->contact->phone),
                        ['class' => 'data-table-secondary-text', 'data-lazy' => 0]
                    );
                }

                return $email . $phone;
            },
        ],
        [
            'attribute' => 'last_activity_at',
            'format' => 'raw',
            'content' => function ($model) {
                /**
                 * @var Staff $model
                 */

                if ($model->account->last_activity_at) {
                    $timeout = Yii::$app->user->authTimeout ? Yii::$app->user->authTimeout : Yii::$app->session->timeout;
                    $isOnline = time() - $model->account->last_activity_at <= $timeout;

                    if ($isOnline) {
                        $status = Html::tag('div', '', [
                            'class' => 'bg-success d-inline-block mr-1 rounded align-middle',
                            'style' => ['width' => '0.8rem', 'height' => '0.8rem'],
                            'title' => Yii::t('app', 'Online'),
                            'data-toggle' => 'tooltip',
                        ]);
                    } else {
                        $status = Html::tag('div', '', [
                            'class' => 'bg-warning d-inline-block mr-1 rounded align-middle',
                            'style' => ['width' => '0.8rem', 'height' => '0.8rem'],
                            'title' => Yii::t('app', 'Offline'),
                            'data-toggle' => 'tooltip',
                        ]);
                    }
                } else {
                    $status = Html::tag('div', '', [
                        'class' => 'bg-secondary d-inline-block mr-1 rounded align-middle',
                        'style' => ['width' => '0.8rem', 'height' => '0.8rem'],
                        'title' => Yii::t('app', 'Never Login'),
                        'data-toggle' => 'tooltip',
                    ]);

                    return $status . Yii::t('app', 'Never Login');
                }

                $date = Html::tag('div', $status . Yii::$app->formatter->asDatetime($model->account->last_activity_at));
                $relativeTime = Html::tag(
                    'div',
                    Yii::$app->formatter->asRelativeTime($model->account->last_activity_at),
                    ['class' => 'data-table-secondary-text']
                );

                return $date . $relativeTime;
            },
        ],
        [
            'attribute' => 'created_at',
            'class' => DateColumn::class,
        ],
        [
            'format' => 'raw',
            'label' => StaffAccount::instance()->getAttributeLabel('is_blocked'),
            'attribute' => 'account.is_blocked',
            'class' => BooleanColumn::class,
            'contentCell' => [
                'vAlign' => Cell::V_ALIGN_CENTER,
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'headerCell' => [
                'hAlign' => Cell::H_ALIGN_CENTER,
            ],
            'buttonOptions' => function ($value) {
                return [
                    'buttonOptions' => [
                        'href' => '#',
                        'class' => ['widget' => 'badge badge-clean text-uppercase p-2 ' . ($value ? 'badge-danger' : 'badge-primary')],
                    ],
                ];
            },
            'trueLabel' => Yii::t('app', 'Blocked'),
            'falseLabel' => Yii::t('app', 'Active'),
            'trueActionLabel' => Icon::show('i8:delete-shield', ['class' => 'icon mr-2']) . Yii::t('app', 'Block'),
            'falseActionLabel' => Icon::show('i8:shield', ['class' => 'icon mr-2']) . Yii::t('app', 'Unblock'),
            'falseItemOptions' => function ($value, $model) {
                return [
                    'linkOptions' => [
                        'title' => Yii::t('app', 'Unblock'),
                        'data-confirmation' => Yii::t('app', 'You are about to unblock {object_name}, are you sure?', [
                            'object_name' => $model->name,
                        ]),
                    ],
                ];
            },
            'trueItemOptions' => function ($value, $model) {
                return [
                    'linkOptions' => [
                        'title' => Yii::t('app', 'Block'),
                        'class' => 'text-danger',
                        'data-confirmation' => Yii::t('app', 'You are about to block {object_name}, are you sure?', [
                            'object_name' => $model->name,
                        ]),
                    ],
                ];
            },
            'url' => function ($value, $model) {
                return ['/account/admin/staff/block', 'id' => $model->id, 'block' => $value];
            },
        ],
        [
            'class' => ActionColumn::class,
            'sort' => 1000000,
            'controller' => '/account/admin/staff',
            'buttons' => [
                'update' => [
                    'value' => [
                        'icon' => 'i8:edit',
                        'data-toggle' => 'tooltip',
                        'label' => Yii::t('app', 'Update'),
                        'data-lazy-container' => '#main-container',
                        'data-lazy-modal' => 'staff-form-modal',
                    ],
                ],
            ],
        ],
    ],
], $dataTableOptions));

echo $this->block('@data-table');

DataTable::end();

echo $this->block('@end', $dataTable);