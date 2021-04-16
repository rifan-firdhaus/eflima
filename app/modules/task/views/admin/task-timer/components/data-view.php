<?php


use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\core\validators\DateValidator;
use modules\task\assets\admin\TaskTimerDataViewAsset;
use modules\task\models\forms\task_timer\TaskTimerSearch;
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
 * @var View            $this
 * @var TaskTimerSearch $searchModel
 * @var array           $dataViewOptions
 * @var StaffAccount    $account
 */

$account = Yii::$app->user->identity;

$addUrl = ArrayHelper::getValue($searchModel->params, 'addUrl', [
    '/task/admin/task-timer/add',
    'task_id' => isset($searchModel->currentTask) ? $searchModel->currentTask->id : null,
]);

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'id' => 'task-timer-data-view',
    'dataProvider' => $searchModel->dataProvider,
    'linkPager' => [
        'pagination' => $searchModel->dataProvider->pagination,
    ],
    'bodyOptions' => [
        'class' => 'card-body p-0',
    ],
    'sort' => $searchModel->dataProvider->sort,
    'clearSearchUrl' => $searchModel->clearSearchUrl(),
    'searchAction' => $searchModel->searchUrl('/task/admin/task-timer/index'),
    'searchFields' => [
        [
            'class' => ContainerField::class,
            'inputOnly' => true,
            'inputOptions' => [
                'class' => 'd-flex',
            ],
            'fields' => [
                [
                    'size' => '',
                    'field' => [
                        'class' => ActiveField::class,
                        'attribute' => 'date_from',
                        'type' => ActiveField::TYPE_WIDGET,
                        'standalone' => true,
                        'placeholder' => Yii::t('app', 'From'),
                        'inputOptions' => [
                            'class' => 'form-control',
                            'id' => Html::getInputId($searchModel, 'date_from') . '-quick',
                            'onchange' => "$(this).closest('form').trigger('submit')",
                        ],
                        'widget' => [
                            'class' => DatepickerInput::class,
                            'type' => DateValidator::TYPE_DATETIME,
                            'range' => [
                                'input' => '#' . Html::getInputId($searchModel, 'date_to') . '-quick',
                            ],
                        ],
                    ],
                ],
                [
                    'size' => 'py-1 px-2 align-self-center justify-self-center',
                    'field' => [
                        'class' => RawField::class,
                        'input' => Yii::t('app', 'To'),
                    ],
                ],
                [
                    'size' => '',
                    'field' => [
                        'class' => ActiveField::class,
                        'attribute' => 'date_to',
                        'label' => Yii::t('app', 'To'),
                        'type' => ActiveField::TYPE_WIDGET,
                        'standalone' => true,
                        'placeholder' => true,
                        'inputOptions' => [
                            'class' => 'form-control flatpickr-input',
                            'id' => Html::getInputId($searchModel, 'date_to') . '-quick',
                            'onchange' => "$(this).closest('form').trigger('submit')",
                        ],
                        'widget' => [
                            'class' => DatepickerInput::class,
                            'type' => DateValidator::TYPE_DATETIME,
                        ],
                    ],
                ],
            ],
        ],
    ],
    'advanceSearchFields' => [
        [
            'class' => CardField::class,
            'fields' => [
                [
                    'attribute' => 'starter_id',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => StaffInput::class,
                        'multiple' => true,
                    ],
                ],
                [
                    'attribute' => 'stopper_id',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => StaffInput::class,
                        'multiple' => true,
                    ],
                ],
                [
                    'class' => ContainerField::class,
                    'label' => Yii::t('app', 'Time Range'),
                    'fields' => [
                        [
                            'size' => 'col-md-6',
                            'field' => [
                                'class' => ActiveField::class,
                                'attribute' => 'date_from',
                                'type' => ActiveField::TYPE_WIDGET,
                                'standalone' => true,
                                'placeholder' => Yii::t('app', 'From'),
                                'widget' => [
                                    'class' => DatepickerInput::class,
                                    'type' => DateValidator::TYPE_DATETIME,
                                    'range' => ['input' => '#' . Html::getInputId($searchModel, 'date_to')],
                                ],
                            ],
                        ],
                        [
                            'size' => 'col-md-6',
                            'field' => [
                                'class' => ActiveField::class,
                                'attribute' => 'date_to',
                                'label' => Yii::t('app', 'To'),
                                'type' => ActiveField::TYPE_WIDGET,
                                'standalone' => true,
                                'placeholder' => true,
                                'inputOptions' => [
                                    'class' => 'form-control flatpickr-input',
                                ],
                                'widget' => [
                                    'class' => DatepickerInput::class,
                                    'type' => DateValidator::TYPE_DATETIME,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
], $dataViewOptions));

echo $this->render('data-statistic', compact('searchModel'));

echo $this->render('data-table', [
    'dataProvider' => $searchModel->dataProvider,
    'params' => $searchModel->params,
]);

$dataView->beginHeader();

if ($addUrl !== false && isset($searchModel->currentTask) && Yii::$app->user->can('admin.task.timer.add')) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), $addUrl, [
        'class' => 'btn btn-primary mr-1',
        'data-lazy-modal' => 'task-timer-form-modal',
        'data-lazy-modal-size' => 'modal-md',
        'data-lazy-container' => '#main-container',
    ]);
}


if (isset($searchModel->currentTask) && $searchModel->currentTask->is_timer_enabled && Yii::$app->user->can('admin.task.timer.toggle')) {
    $isTimerStarted = $searchModel->currentTask->isTimerStarted($account->profile->id);

    echo Html::a([
        'url' => ['/task/admin/task/toggle-timer', 'id' => $searchModel->currentTask->id, 'start' => !$isTimerStarted],
        'icon' => !$isTimerStarted ? 'i8:play' : 'i8:stop',
        'title' => !$isTimerStarted ? Yii::t('app', 'Start Timer') : Yii::t('app', 'Stop Timer'),
        'class' => 'btn btn-primary btn-icon',
        'data-toggle' => 'tooltip',
        'data-lazy-options' => ['method' => 'POST'],
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
                'label' => Yii::t('app', 'Delete'),
                'url' => ['/task/admin/task-timer/bulk-delete'],
                'linkOptions' => [
                    'class' => 'bulk-delete text-danger',
                    'title' => Yii::t('app', 'Bulk Delete'),
                    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                        'object_name' => Yii::t('app', 'selected {object}', [
                            'object' => Yii::t('app', 'Timer'),
                        ]),
                    ]),
                    'data-lazy-options' => ['method' => 'DELETE'],
                ],
            ],
        ],
    ],
]);

$dataView->endHeader();

TaskTimerDataViewAsset::register($this);

$this->registerJs("$('#{$dataView->getId()}').taskTimerDataView()");

DataView::end();

echo $this->block('@begin');
