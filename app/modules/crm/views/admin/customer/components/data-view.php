<?php


use modules\account\web\admin\View;
use modules\address\widgets\inputs\CountryInput;
use modules\crm\assets\admin\CustomerDataViewAsset;
use modules\crm\models\Customer;
use modules\crm\models\forms\customer\CustomerSearch;
use modules\ui\widgets\ButtonDropdown;
use modules\ui\widgets\DataView;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View           $this
 * @var CustomerSearch $searchModel
 * @var array          $dataViewOptions
 */

$dataProvider = $searchModel->dataProvider;

$addUrl = ArrayHelper::getValue($searchModel->params, 'addUrl', ['/crm/admin/customer/add']);

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'id' => 'customer-data-view',
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
    'searchAction' => $searchModel->searchUrl('/crm/admin/customer/index'),
    'advanceSearchFields' => [
        [
            'class' => CardField::class,
            'fields' => [
                [
                    'attribute' => 'q',
                ],
                [
                    'attribute' => 'type',
                    'type' => ActiveField::TYPE_RADIO_LIST,
                    'source' => array_merge(['' => Yii::t('app', 'All Type')], Customer::types()),
                    'inputOptions' => [
                        'itemOptions' => [
                            'inline' => true,
                            'custom' => true,
                        ],
                        'class' => 'h-100 d-flex align-items-center',
                    ],
                ],
                [
                    'attribute' => 'country_code',
                    'type' => ActiveField::TYPE_WIDGET,
                    'widget' => [
                        'class' => CountryInput::class,
                        'allowClear' => true,
                        'jsOptions' => [
                            'width' => '100%',
                        ],
                    ],
                ],
            ],
        ],
    ],
], $dataViewOptions));

echo $this->render('data-table', [
    'dataProvider' => $dataProvider,
    'paramss' => $searchModel->params,
]);

$dataView->beginHeader();

if ($addUrl !== false && Yii::$app->user->can('admin.customer.add')) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), $addUrl, [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'customer-form-modal',
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
                'label' => Icon::show('i8:hashtag', ['class' => 'icon mr-2']) .Yii::t('app', 'Set Group'),
                'encode' => false,
                'url' => ['/crm/admin/customer/bulk-set-group'],
                'linkOptions' => [
                    'class' => 'bulk-set-group',
                    'data-lazy-modal' => 'customer-bulk-set-group-form-modal',
                    'data-lazy-modal-size' => 'modal-sm',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-options' => ['method' => 'POST'],
                ],
            ],
            '-',
            [
                'label' => Icon::show('i8:trash', ['class' => 'icon mr-2']) .Yii::t('app', 'Delete'),
                'encode' => false,
                'url' => ['/crm/admin/customer/bulk-delete'],
                'linkOptions' => [
                    'class' => 'bulk-delete text-danger',
                    'title' => Yii::t('app', 'Bulk Delete'),
                    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
                        'object_name' => Yii::t('app', 'selected {object}', [
                            'object' => Yii::t('app', 'Customer'),
                        ]),
                    ]),
                    'data-lazy-options' => ['method' => 'DELETE'],
                ],
            ],
        ],
    ],
]);

$dataView->endHeader();

CustomerDataViewAsset::register($this);

$this->registerJs("$('#{$dataView->getId()}').customerDataView()");

DataView::end();

echo $this->block('@end');
