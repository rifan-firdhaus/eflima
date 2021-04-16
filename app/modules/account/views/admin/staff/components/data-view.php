<?php


use modules\account\assets\admin\StaffDataViewAsset;
use modules\account\models\forms\staff\StaffSearch;
use modules\account\web\admin\View;
use modules\ui\widgets\DataView;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\fields\InputField;
use modules\ui\widgets\Icon;
use modules\ui\widgets\inputs\DatepickerInput;
use yii\bootstrap4\ButtonDropdown;
use yii\bootstrap4\ButtonGroup;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View               $this
 * @var StaffSearch        $searchModel
 * @var array              $dataViewOptions
 */

$dataProvider = $searchModel->dataProvider;

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'id' => 'staff-data-view',
    'linkPager' => [
        'pagination' => $dataProvider->pagination,
    ],
    'mainSearchField' => [
        'attribute' => 'q',
    ],
    'bodyOptions' => [
        'class' => 'card-body p-0',
    ],
    'sort' => $dataProvider->sort,
    'clearSearchUrl' => $searchModel->clearSearchUrl(),
    'searchAction' => $searchModel->searchUrl('/account/admin/staff/index'),
    'advanceSearchFields' => [
        [
            'class' => CardField::class,
            'fields' => [
                [
                    'class' => ActiveField::class,
                    'attribute' => 'q',
                ],
                [
                    'class' => ContainerField::class,
                    'label' => Yii::t('app', 'Created at'),
                    'fields' => [
                        [
                            'size' => 'col-md-6',
                            'field' => [
                                'class' => ActiveField::class,
                                'attribute' => 'created_at_from',
                                'type' => ActiveField::TYPE_WIDGET,
                                'widget' => [
                                    'class' => DatepickerInput::class,
                                ],
                                'standalone' => true,
                                'inputGroups' => ['before' => Icon::show('i8:calendar', ['class' => 'icon icons8-size'])],
                            ],
                        ],
                        [
                            'size' => 'col-md-6',
                            'field' => [
                                'class' => ActiveField::class,
                                'attribute' => 'created_at_to',
                                'type' => ActiveField::TYPE_WIDGET,
                                'widget' => [
                                    'class' => DatepickerInput::class,
                                ],
                                'standalone' => true,
                                'inputGroups' => ['before' => Icon::show('i8:calendar', ['class' => 'icon icons8-size'])],
                            ],
                        ],
                    ],
                ],
                [
                    'class' => ActiveField::class,
                    'attribute' => 'is_blocked',
                    'type' => InputField::TYPE_RADIO_LIST,
                    'inputOptions' => [
                        'itemOptions' => [
                            'custom' => true,
                            'inline' => true,
                            'containerOptions' => [
                                'class' => 'mb-2',
                            ],
                        ],
                    ],
                    'source' => [
                        '' => Yii::t('app', 'Don\'t filter'),
                        '1' => Yii::t('app', 'Show only blocked'),
                        '0' => Yii::t('app', 'Show only non blocked'),
                    ],
                ],
            ],
        ],

    ],
], $dataViewOptions));

echo $this->render('data-table', compact('dataProvider'));


$dataView->beginHeader();

if(Yii::$app->user->can('admin.staff.add')){
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), ['/account/admin/staff/add'], [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'staff-form-modal',
        'data-lazy-container' => '#main-container',
    ]);
}

echo ButtonDropdown::widget([
    'label' => Yii::t('app', 'Bulk Action'),
    'options' => [
        'class' => 'bulk-actions',
    ],
    'buttonOptions' => [
        'class' => 'ml-1 btn-outline-primary',
    ],
    'dropdown' => [
        'items' => [
            [
                'label' => Icon::show('i8:multiply',['class' => 'icon mr-2']).Yii::t('app', 'Block'),
                'encode' => false,
                'url' => ['/account/admin/staff/bulk-block','block' => 1],
                'linkOptions' => [
                    'class' => 'bulk-block',
                    'title' => Yii::t('app', '{action} Selected {object}',[
                        'action' => Yii::t('app', 'Block'),
                        'object' => Yii::t('app', 'Staff')
                    ]),
                    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                        'object_name' => Yii::t('app', 'selected {object}', [
                            'object' => Yii::t('app', 'Staff'),
                        ]),
                    ]),
                    'data-lazy-options' => ['method' => 'DELETE'],
                ],
            ],
            [
                'label' => Icon::show('i8:checkmark',['class' => 'icon mr-2']).Yii::t('app', 'Unblock'),
                'encode' => false,
                'url' => ['/account/admin/staff/bulk-block','block' => 0],
                'linkOptions' => [
                    'class' => 'bulk-unblock',
                    'title' => Yii::t('app', '{action} Selected {object}',[
                        'action' => Yii::t('app', 'Unblock'),
                        'object' => Yii::t('app', 'Staff')
                    ]),
                    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                        'object_name' => Yii::t('app', 'selected {object}', [
                            'object' => Yii::t('app', 'Staff'),
                        ]),
                    ]),
                    'data-lazy-options' => ['method' => 'DELETE'],
                ],
            ],
            '-',
            [
                'label' => Icon::show('i8:trash',['class' => 'icon mr-2']).Yii::t('app', 'Delete'),
                'encode' => false,
                'url' => ['/account/admin/staff/bulk-delete'],
                'linkOptions' => [
                    'class' => 'bulk-delete text-danger',
                    'title' => Yii::t('app', 'Bulk Delete'),
                    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                        'object_name' => Yii::t('app', 'selected {object}', [
                            'object' => Yii::t('app', 'Staff'),
                        ]),
                    ]),
                    'data-lazy-options' => ['method' => 'DELETE'],
                ],
            ],
        ],
    ],
]);

$dataView->endHeader();

StaffDataViewAsset::register($this);

$this->registerJs("$('#{$dataView->getId()}').staffDataView()");

DataView::end();

echo $this->block('@end');
