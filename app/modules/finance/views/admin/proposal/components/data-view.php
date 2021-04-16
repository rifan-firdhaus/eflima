<?php


use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\finance\assets\admin\ProposalDataViewAsset;
use modules\finance\models\forms\proposal\ProposalSearch;
use modules\finance\widgets\inputs\ProposalStatusInput;
use modules\ui\widgets\ButtonDropdown;
use modules\ui\widgets\DataView;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\Icon;
use modules\ui\widgets\inputs\DatepickerInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View           $this
 * @var ProposalSearch $searchModel
 * @var array          $dataViewOptions
 */

$dataProvider = $searchModel->dataProvider;

$addUrl = ArrayHelper::getValue($searchModel->params, 'addUrl', ['/finance/admin/proposal/add']);

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'id' => 'proposal-data-view',
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
    'searchAction' => $searchModel->searchUrl('/finance/admin/proposal/index'),
    'advanceSearchFields' => [
        [
            'class' => CardField::class,
            'fields' => [
                [
                    'attribute' => 'status_id',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => ProposalStatusInput::class,
                        'multiple' => true,
                    ],
                ],
                [
                    'class' => ContainerField::class,
                    'label' => Yii::t('app', 'Date'),
                    'fields' => [
                        [
                            'size' => 'col-md-6',
                            'field' => [
                                'class' => ActiveField::class,
                                'attribute' => 'date_from',
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
                                'attribute' => 'date_to',
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
                    'label' => Yii::t('app', 'Created At'),
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
                    'attribute' => 'assignee_ids',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => StaffInput::class,
                        'multiple' => true,
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

echo $this->render('data-table', [
    'dataProvider' => $dataProvider,
    'params' => $searchModel->params,
]);

$dataView->beginHeader();

if ($addUrl !== false && Yii::$app->user->can('admin.proposal.add')) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), $addUrl, [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'proposal-form-modal',
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
                'label' => Icon::show('i8:hammer', ['class' => 'icon mr-2']) .Yii::t('app', 'Set Status'),
                'encode' => false,
                'url' => ['/finance/admin/proposal/bulk-set-status'],
                'linkOptions' => [
                    'class' => 'bulk-set-status',
                    'data-lazy-modal' => 'proposal-bulk-set-status-form-modal',
                    'data-lazy-modal-size' => 'modal-sm',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-options' => ['method' => 'POST'],
                ],
            ],
            '-',
            [
                'label' => Icon::show('i8:trash', ['class' => 'icon mr-2']) .Yii::t('app', 'Delete'),
                'encode' => false,
                'url' => ['/finance/admin/proposal/bulk-delete'],
                'linkOptions' => [
                    'class' => 'bulk-delete text-danger',
                    'title' => Yii::t('app', 'Bulk Delete'),
                    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                        'object_name' => Yii::t('app', 'selected {object}', [
                            'object' => Yii::t('app', 'Proposals'),
                        ]),
                    ]),
                    'data-lazy-options' => ['method' => 'DELETE'],
                ],
            ],
        ],
    ],
]);

$dataView->endHeader();

ProposalDataViewAsset::register($this);

$this->registerJs("$('#{$dataView->getId()}').proposalDataView()");

DataView::end();

echo $this->block('@end');
