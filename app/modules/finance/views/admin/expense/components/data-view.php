<?php


use modules\account\web\admin\View;
use modules\core\validators\DateValidator;
use modules\crm\widgets\inputs\CustomerInput;
use modules\finance\models\forms\expense\ExpenseSearch;
use modules\finance\widgets\inputs\CurrencyInput;
use modules\finance\widgets\inputs\ExpenseCategoryInput;
use modules\ui\widgets\DataView;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\fields\RawField;
use modules\ui\widgets\Icon;
use modules\ui\widgets\inputs\DatepickerInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;


/**
 * @var View          $this
 * @var ExpenseSearch $searchModel
 * @var array         $dataViewOptions
 */

$dataProvider = $searchModel->dataProvider;

$addUrl = ArrayHelper::getValue($searchModel->params, 'addUrl', [
    '/finance/admin/expense/add',
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
    'id' => 'expense-data-view',
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
    'searchAction' => $searchModel->searchUrl('/finance/admin/expense/index', [
        'view' => Yii::$app->request->get('view'),
        'customer_id' => Yii::$app->request->get('customer_id'),
    ], false),
    'searchFields' => [
        [
            'class' => ContainerField::class,
            'inputOnly' => true,
            'inputOptions' => [
                'class' => 'd-flex mr-3',
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
                        ],
                        'inputGroups' => [
                            [
                                'position' => 'prepend',
                                'content' => Icon::show('i8:calendar', ['class' => 'icons8-size']),
                            ],
                        ],
                        'widget' => [
                            'class' => DatepickerInput::class,
                            'type' => DateValidator::TYPE_DATE,
                            'range' => [
                                'input' => '#' . Html::getInputId($searchModel, 'date_to') . '-quick',
                            ],
                            'jsOptions' => [
                                'language' => 'en',
                                'autoClose' => true,
                                'onClose' => $onSearchDateClose,
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
                        ],
                        'inputGroups' => [
                            [
                                'position' => 'prepend',
                                'content' => Icon::show('i8:calendar', ['class' => 'icons8-size']),
                            ],
                        ],
                        'widget' => [
                            'class' => DatepickerInput::class,
                            'type' => DateValidator::TYPE_DATE,
                            'jsOptions' => [
                                'language' => 'en',
                                'autoClose' => true,
                                'onClose' => $onSearchDateClose,
                            ],
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
                    'attribute' => 'q',
                ],
                [
                    'attribute' => 'customer_id',
                    'visible' => empty($searchModel->params['customer_id']),
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => CustomerInput::class,
                        'multiple' => true,
                        'allowClear' => true,
                    ],
                ],
                [
                    'attribute' => 'category_id',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => ExpenseCategoryInput::class,
                        'multiple' => true,
                        'allowAdd' => false,
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
                                'standalone' => true,
                                'placeholder' => Yii::t('app', 'From'),
                                'widget' => [
                                    'class' => DatepickerInput::class,
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
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'attribute' => 'currency_code',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => CurrencyInput::class,
                        'multiple' => true,
                    ],
                ],
                [
                    'attribute' => 'is_billable',
                    'label' => '',
                    'type' => ActiveField::TYPE_RADIO_LIST,
                    'source' => [
                        '1' => Yii::t('app', 'Only Billable Expense'),
                        '0' => Yii::t('app', 'Only Non Billable Expense'),
                    ],
                    'inputOptions' => [
                        'itemOptions' => [
                            'containerOptions' => [
                                'class' => 'my-1',
                            ],
                            'custom' => true,
                        ],
                    ],
                ],
                [
                    'attribute' => 'is_billed',
                    'label' => '',
                    'type' => ActiveField::TYPE_RADIO_LIST,
                    'source' => [
                        '1' => Yii::t('app', 'Only Billed Expense'),
                        '0' => Yii::t('app', 'Only Not Billed Expense'),
                    ],
                    'inputOptions' => [
                        'itemOptions' => [
                            'containerOptions' => [
                                'class' => 'my-1',
                            ],
                            'custom' => true,
                        ],
                    ],
                ],
            ],
        ],
    ],
], $dataViewOptions));

if (empty($searchModel->params['customer_id']) && (!isset($searchModel->params['periodicallyWidget']) || $searchModel->params['periodicallyWidget'] === true)) {
    echo $this->render('data-statistic', [
        'searchModel' => $searchModel,
        'searchAction' => $dataView->searchAction,
    ]);
}

echo Html::tag('div', $this->render('data-bill-statistic', [
    'searchModel' => $searchModel,
    'searchAction' => $dataView->searchAction,
]),['class' => 'border-top']);

echo $this->render('data-table', compact('dataProvider', 'searchModel'));

$dataView->beginHeader();

if ($addUrl !== false) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), $addUrl, [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'expense-form-modal',
        'data-lazy-container' => '#main-container',
    ]);
}

$dataView->endHeader();

DataView::end();

echo $this->block('@end');
