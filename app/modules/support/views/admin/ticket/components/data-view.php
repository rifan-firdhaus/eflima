<?php


use modules\account\web\admin\View;
use modules\crm\widgets\inputs\CustomerInput;
use modules\support\models\forms\ticket\TicketSearch;
use modules\support\widgets\inputs\TicketDepartmentInput;
use modules\support\widgets\inputs\TicketPriorityInput;
use modules\support\widgets\inputs\TicketStatusInput;
use modules\ui\widgets\DataView;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\Icon;
use modules\ui\widgets\inputs\DatepickerInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View         $this
 * @var TicketSearch $searchModel
 * @var array        $dataViewOptions
 */

$dataProvider = $searchModel->dataProvider;

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

$addUrl = ArrayHelper::getValue($searchModel->params, 'addUrl', [
    '/support/admin/ticket/add',
    'customer_id' => isset($searchModel->params['customer_id']) ? $searchModel->params['customer_id'] : null,
]);

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'id' => 'ticket-data-view',
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
    'searchAction' => $searchModel->searchUrl('/support/admin/ticket/index'),
    'advanceSearchFields' => [
        [
            'class' => CardField::class,
            'fields' => [
                [
                    'attribute' => 'status_id',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => TicketStatusInput::class,
                        'multiple' => true,
                    ],
                ],
                [
                    'attribute' => 'priority_id',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => TicketPriorityInput::class,
                        'multiple' => true,
                    ],
                ],
                [
                    'attribute' => 'department_id',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => TicketDepartmentInput::class,
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
                    'label' => Yii::t('app', 'Created Date'),
                    'fields' => [
                        [
                            'size' => 'col-md-6',
                            'field' => [
                                'class' => ActiveField::class,
                                'attribute' => 'created_at_from',
                                'type' => ActiveField::TYPE_WIDGET,
                                'standalone' => true,
                                'placeholder' => Yii::t('app', 'From'),
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
            ],
        ],
    ],
], $dataViewOptions));

echo $this->render('data-statistic', [
    'searchModel' => $searchModel,
    'searchAction' => $dataView->searchAction,
]);

echo $this->render('data-table', [
    'dataProvider' => $dataProvider,
    'params' => $searchModel->params,
]);

$dataView->beginHeader();

if ($addUrl !== false) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), $addUrl, [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'ticket-form-modal',
        'data-lazy-modal-size' => 'modal-lg',
        'data-lazy-container' => '#main-container',
    ]);
}

$dataView->endHeader();

DataView::end();

echo $this->block('@begin');