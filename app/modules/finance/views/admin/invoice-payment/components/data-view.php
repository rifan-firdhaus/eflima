<?php


use modules\account\web\admin\View;
use modules\crm\widgets\inputs\CustomerInput;
use modules\finance\components\Payment;
use modules\finance\models\forms\invoice_payment\InvoicePaymentSearch;
use modules\ui\widgets\DataView;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View                 $this
 * @var InvoicePaymentSearch $searchModel
 * @var array                $dataViewOptions
 * @var array                $configurations
 */

$dataProvider = $searchModel->dataProvider;

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

if (!isset($configurations)) {
    $configurations = [];
}

$addUrl = ArrayHelper::getValue($searchModel->params, 'addUrl', [
    '/finance/admin/invoice-payment/add',
    'customer_id' => isset($searchModel->params['customer_id']) ? $searchModel->params['customer_id'] : null,
    'invoice_id' => isset($searchModel->params['invoice_id']) ? $searchModel->params['invoice_id'] : null,
]);

$configurations = ArrayHelper::merge([
    'statistic' => true,
], $configurations);

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
    'configurations' => &$configurations,
]);

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'id' => 'invoice-payment-data-view',
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
    'searchAction' => $searchModel->searchUrl('/finance/admin/invoice-payment/index', [
        'view' => Yii::$app->request->get('view'),
        'customer_id' => Yii::$app->request->get('customer_id'),
    ], false),
    'advanceSearchFields' => [
        [
            'class' => CardField::class,
            'fields' => [
                [
                    'attribute' => 'q',
                ],
                [
                    'attribute' => 'customer_id',
                    'visible' => empty($searchModel->params['customer_id']) && empty($searchModel->params['invoice_id']),
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => CustomerInput::class,
                        'multiple' => true,
                        'allowClear' => true,
                    ],
                ],
                [
                    'attribute' => 'method_id',
                    'type' => ActiveField::TYPE_CHECKBOX_LIST,
                    'source' => Payment::map(),
                    'inputOptions' => [
                        'class' => 'h-100 d-flex align-items-center',
                        'itemOptions' => [
                            'custom' => true,
                            'inline' => true,
                        ],
                    ],
                ],
            ],
        ],
    ],
], $dataViewOptions));

echo $this->block('@data-view:begin');

if ($configurations['statistic']) {
    echo $this->render('data-statistic', [
        'searchModel' => $searchModel,
        'searchAction' => $dataView->searchAction,
    ]);
}

echo $this->render('data-table', compact('dataProvider', 'searchModel'));

$dataView->beginHeader();

if ($addUrl !== false) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), $addUrl, [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'invoice-payment-form',
        'data-lazy-modal-size' => 'modal-md',
        'data-lazy-container' => '#main-container',
    ]);
}

$dataView->endHeader();

echo $this->block('@data-view:end');

DataView::end();

echo $this->block('@begin');