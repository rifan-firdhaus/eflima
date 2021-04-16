<?php


use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\address\widgets\inputs\CountryInput;
use modules\crm\assets\admin\LeadDataViewAsset;
use modules\crm\models\forms\lead\LeadSearch;
use modules\crm\widgets\inputs\LeadSourceInput;
use modules\crm\widgets\inputs\LeadStatusInput;
use modules\ui\widgets\ButtonDropdown;
use modules\ui\widgets\DataView;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\fields\RawField;
use modules\ui\widgets\Icon;
use modules\ui\widgets\inputs\DatepickerInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View       $this
 * @var LeadSearch $searchModel
 * @var array      $dataViewOptions
 */

$dataProvider = $searchModel->dataProvider;

$addUrl = ArrayHelper::getValue($searchModel->params, 'addUrl', ['/crm/admin/lead/add']);

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'id' => 'lead-data-view',
    'dataProvider' => $dataProvider,
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
    'searchAction' => $searchModel->searchUrl('/crm/admin/lead/index', [], false),
    'advanceSearchFields' => [
        [
            'class' => CardField::class,
            'fields' => [
                [
                    'attribute' => 'q',
                ],
                [
                    'attribute' => 'status_id',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => LeadStatusInput::class,
                        'multiple' => true,
                    ],
                ],
                [
                    'attribute' => 'source_id',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => LeadSourceInput::class,
                        'multiple' => true,
                    ],
                ],
                [
                    'attribute' => 'country_code',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => CountryInput::class,
                        'multiple' => true,
                    ],
                ],
                [
                    'attribute' => 'assignee_ids',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => StaffInput::class,
                        'multiple' => true,
                    ],
                ],
                [
                    'class' => ContainerField::class,
                    'label' => Yii::t('app', 'Created Date'),
                    'fields' => [
                        [
                            'size' => 'col-md-6',
                            'field' => [
                                'class' => ActiveField::class,
                                'attribute' => 'created_at_from',
                                'type' => ActiveField::TYPE_WIDGET,
                                'placeholder' => Yii::t('app', 'From'),
                                'standalone' => true,
                                'widget' => [
                                    'class' => DatepickerInput::class,
                                    'range' => ['input' => '#' . Html::getInputId($searchModel, 'created_at_to')],
                                ],
                            ],
                        ],
                        [
                            'size' => 'col-md-6',
                            'field' => [
                                'class' => ActiveField::class,
                                'attribute' => 'created_at_to',
                                'label' => Yii::t('app', 'To'),
                                'type' => ActiveField::TYPE_WIDGET,
                                'placeholder' => true,
                                'standalone' => true,
                                'inputOptions' => [
                                    'class' => 'form-control flatpickr-input',
                                ],
                                'widget' => [
                                    'class' => DatepickerInput::class,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => ActiveField::TYPE_CHECKBOX,
                    'label' => '',
                    'attribute' => 'assigned_to_me',
                    'inputOptions' => [
                        'custom' => true,
                    ],
                ],
            ],
        ],
    ],
], $dataViewOptions));



$dataView->searchFields[] = [
    'class' => RawField::class,
    'inputOnly' => true,
    'sort' => -1,
    'input' => Html::a([
        'url' => $searchModel->searchUrl($dataView->searchAction, [
            $searchModel->formName() => [
                'assigned_to_me' => $searchModel->assigned_to_me ? 0 : 1,
            ],
        ]),
        'label' => Html::tag('span', Yii::t('app', 'Only Assigned to Me'), ['class' => 'btn-label']),
        'class' => 'btn btn-icon-sm btn-outline-primary mr-2 ' . ($searchModel->assigned_to_me ? 'active' : null),
        'icon' => 'i8:apply',
    ]),
];

echo $this->render('data-statistic', [
    'searchModel' => $searchModel,
    'searchAction' => $dataView->searchAction,
]);

echo $this->render('data-table', [
    'dataProvider' => $dataProvider,
    'params' => $searchModel->params,
]);

$dataView->beginHeader();

if ($addUrl !== false && Yii::$app->user->can('admin.lead.add')) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), $addUrl, [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'lead-form-modal',
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
                'label' => Icon::show('i8:hammer', ['class' => 'icon mr-2']) . Yii::t('app', 'Set Status'),
                'encode' => false,
                'url' => ['/crm/admin/lead/bulk-set-status'],
                'linkOptions' => [
                    'class' => 'bulk-set-status',
                    'data-lazy-modal' => 'lead-bulk-set-status-form-modal',
                    'data-lazy-modal-size' => 'modal-sm',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-options' => ['method' => 'POST'],
                ],
            ],
            [
                'label' => Icon::show('i8:link', ['class' => 'icon mr-2']) . Yii::t('app', 'Reassign'),
                'encode' => false,
                'url' => ['/crm/admin/lead/bulk-reassign'],
                'linkOptions' => [
                    'class' => 'bulk-reassign',
                    'data-lazy-modal' => 'task-bulk-reassign-form-modal',
                    'data-lazy-modal-size' => 'modal-md',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-options' => ['method' => 'POST'],
                ],
            ],
            '-',
            [
                'label' => Icon::show('i8:trash', ['class' => 'icon mr-2']) . Yii::t('app', 'Delete'),
                'encode' => false,
                'url' => ['/crm/admin/lead/bulk-delete'],
                'linkOptions' => [
                    'class' => 'bulk-delete text-danger',
                    'title' => Yii::t('app', 'Bulk Delete'),
                    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                        'object_name' => Yii::t('app', 'selected {object}', [
                            'object' => Yii::t('app', 'Lead'),
                        ]),
                    ]),
                    'data-lazy-options' => ['method' => 'DELETE'],
                ],
            ],
        ],
    ],
]);

$dataView->endHeader();

LeadDataViewAsset::register($this);

$this->registerJs("$('#{$dataView->getId()}').leadDataView()");

DataView::end();

echo $this->block('@end');
