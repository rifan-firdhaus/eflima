<?php


use modules\account\web\admin\View;
use modules\crm\widgets\inputs\CustomerInput;
use modules\project\models\forms\project\ProjectSearch;
use modules\project\widgets\inputs\ProjectStatusInput;
use modules\ui\widgets\DataView;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\Icon;
use modules\ui\widgets\inputs\DatepickerInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;


/**
 * @var View          $this
 * @var ProjectSearch $searchModel
 * @var array         $dataViewOptions
 */

$addUrl = ArrayHelper::getValue($searchModel->params, 'addUrl', [
    '/project/admin/project/add',
    'customer_id' => isset($searchModel->params['customer_id']) ? $searchModel->params['customer_id'] : null,
]);

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$onSearchDateClose = new JsExpression('function(){$(this.element).closest("form").trigger("submit")}');

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'id' => 'project-data-view',
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
    'searchAction' => $searchModel->searchUrl('/project/admin/project/index', [
        'view' => Yii::$app->request->get('view'),
        'customer_id' => Yii::$app->request->get('customer_id'),
    ]),
    'searchFields' => [

    ],
    'advanceSearchFields' => [
        [
            'class' => CardField::class,
            'fields' => [
                [
                    'attribute' => 'status_id',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => ProjectStatusInput::class,
                        'multiple' => true,
                    ],
                ],
                [
                    'attribute' => 'customer_id',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => CustomerInput::class,
                        'multiple' => true,
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
            ],
        ],
    ],
], $dataViewOptions));

echo $this->render('data-table', [
    'dataProvider' => $searchModel->dataProvider,
]);

$dataView->beginHeader();

if ($addUrl !== false) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), $addUrl, [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'project-form-modal',
        'data-lazy-container' => '#main-container',
    ]);
}

$dataView->endHeader();

DataView::end();

echo $this->block('@end');
