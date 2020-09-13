<?php


use modules\account\web\admin\View;
use modules\address\widgets\inputs\CountryInput;
use modules\crm\models\Customer;
use modules\crm\models\forms\customer\CustomerSearch;
use modules\ui\widgets\DataView;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\Icon;
use yii\bootstrap4\ButtonGroup;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View           $this
 * @var CustomerSearch $searchModel
 * @var array          $dataViewOptions
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

echo $this->render('data-table', compact('dataProvider', 'searchModel'));

$dataView->beginHeader();

echo ButtonGroup::widget([
    'buttons' => [
        Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), ['/crm/admin/customer/add'], [
            'class' => 'btn btn-primary',
            'data-lazy-modal' => 'customer-form-modal',
            'data-lazy-container' => '#main-container',
        ]),
    ],
]);

$dataView->endHeader();

DataView::end();

echo $this->block('@begin');