<?php


use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\task\assets\admin\TaskDataViewAsset;
use modules\task\models\forms\task\TaskSearch;
use modules\task\widgets\inputs\TaskPriorityInput;
use modules\task\widgets\inputs\TaskStatusInput;
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
 * @var View         $this
 * @var TaskSearch   $searchModel
 * @var array        $dataViewOptions
 */

$account = Yii::$app->user->identity;

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

$addUrl = ArrayHelper::getValue($searchModel->params, 'addUrl', [
    '/task/admin/task/add',
    'model' => isset($searchModel->params['model']) ? $searchModel->params['model'] : null,
    'model_id' => isset($searchModel->params['model_id']) ? $searchModel->params['model_id'] : null,
]);

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'id' => 'task-data-view',
    'dataProvider' => $searchModel->dataProvider,
    'linkPager' => [
        'pagination' => $searchModel->dataProvider->pagination,
    ],
    'mainSearchField' => [
        'attribute' => 'q',
    ],
    'bodyOptions' => [
        'class' => 'card-body p-0',
    ],
    'sort' => $searchModel->dataProvider->sort,
    'clearSearchUrl' => $searchModel->clearSearchUrl(),
    'searchAction' => $searchModel->searchUrl('/task/admin/task/index', [], false),
    'searchFields' => [

    ],
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
                        'class' => TaskStatusInput::class,
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
                    'attribute' => 'priority_id',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => TaskPriorityInput::class,
                    ],
                ],
                [
                    'class' => ContainerField::class,
                    'label' => Yii::t('app', 'Started Date'),
                    'fields' => [
                        [
                            'size' => 'col-md-6',
                            'field' => [
                                'class' => ActiveField::class,
                                'attribute' => 'started_date_from',
                                'type' => ActiveField::TYPE_WIDGET,
                                'standalone' => true,
                                'placeholder' => Yii::t('app', 'From'),
                                'widget' => [
                                    'class' => DatepickerInput::class,
                                    'range' => ['input' => '#' . Html::getInputId($searchModel, 'started_date_to')],
                                ],
                            ],
                        ],
                        [
                            'size' => 'col-md-6',
                            'field' => [
                                'class' => ActiveField::class,
                                'attribute' => 'started_date_to',
                                'label' => Yii::t('app', 'To'),
                                'type' => ActiveField::TYPE_WIDGET,
                                'standalone' => true,
                                'placeholder' => true,
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
                    'class' => ContainerField::class,
                    'label' => Yii::t('app', 'Deadline'),
                    'fields' => [
                        [
                            'size' => 'col-md-6',
                            'field' => [
                                'class' => ActiveField::class,
                                'attribute' => 'deadline_date_from',
                                'type' => ActiveField::TYPE_WIDGET,
                                'standalone' => true,
                                'placeholder' => Yii::t('app', 'From'),
                                'widget' => [
                                    'class' => DatepickerInput::class,
                                    'range' => ['input' => '#' . Html::getInputId($searchModel, 'deadline_date_to')],
                                ],
                            ],
                        ],
                        [
                            'size' => 'col-md-6',
                            'field' => [
                                'class' => ActiveField::class,
                                'attribute' => 'deadline_date_to',
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
                [
                    'type' => ActiveField::TYPE_CHECKBOX,
                    'label' => '',
                    'attribute' => 'overdue',
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
    'dataProvider' => $searchModel->dataProvider,
    'params' => $searchModel->params,
]);

$dataView->beginHeader();

echo Html::beginTag('div', [
    'id' => 'task-data-view-actions',
    'class' => ' flex-shrink-0',
]);

if ($addUrl !== false && Yii::$app->user->can('admin.task.add')) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), $addUrl, [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'task-form-modal',
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
                'label' => Icon::show('i8:hammer',['class' => 'icon mr-2']).Yii::t('app', 'Set Status'),
                'encode' => false,
                'url' => ['/task/admin/task/bulk-set-status'],
                'linkOptions' => [
                    'class' => 'bulk-set-status',
                    'data-lazy-modal' => 'task-bulk-set-status-form-modal',
                    'data-lazy-modal-size' => 'modal-sm',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-options' => ['method' => 'POST'],
                ],
            ],
            [
                'label' => Icon::show('i8:sorting',['class' => 'icon mr-2']).Yii::t('app', 'Set Priority'),
                'encode' => false,
                'url' => ['/task/admin/task/bulk-set-priority'],
                'linkOptions' => [
                    'class' => 'bulk-set-priority',
                    'data-lazy-modal' => 'task-bulk-set-priority-form-modal',
                    'data-lazy-modal-size' => 'modal-sm',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-options' => ['method' => 'POST'],
                ],
            ],
            [
                'label' => Icon::show('i8:link',['class' => 'icon mr-2']).Yii::t('app', 'Reassign'),
                'encode' => false,
                'url' => ['/task/admin/task/bulk-reassign'],
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
                'label' => Icon::show('i8:trash',['class' => 'icon mr-2']).Yii::t('app', 'Delete'),
                'encode' => false,
                'url' => ['/task/admin/task/bulk-delete'],
                'linkOptions' => [
                    'class' => 'bulk-delete text-danger',
                    'title' => Yii::t('app', 'Bulk Delete'),
                    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                        'object_name' => Yii::t('app', 'selected {object}', [
                            'object' => Yii::t('app', 'Task'),
                        ]),
                    ]),
                    'data-lazy-options' => ['method' => 'DELETE'],
                ],
            ],
        ],
    ],
]);

echo Html::endTag('div');

$dataView->endHeader();

TaskDataViewAsset::register($this);

$this->registerJs("$('#{$dataView->getId()}').taskDataView()");

DataView::end();

echo $this->block('@begin');
