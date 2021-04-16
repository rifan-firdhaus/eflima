<?php


use modules\account\web\admin\View;
use modules\crm\models\forms\customer_contact\CustomerContactSearch;
use modules\ui\widgets\DataView;
use modules\ui\widgets\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View                  $this
 * @var CustomerContactSearch $searchModel
 * @var array                 $dataViewOptions
 */

$dataProvider = $searchModel->dataProvider;

$addUrl = ArrayHelper::getValue($searchModel->params, 'addUrl', [
    '/crm/admin/customer-contact/add',
    'customer_id' => !empty($searchModel->params['customer_id']) ? $searchModel->params['customer_id'] : null,
]);

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'id' => 'customer-contact-data-view',
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
    'searchAction' => $searchModel->searchUrl('/crm/admin/customer-contact/index'),
], $dataViewOptions));

echo $this->render('data-table', [
    'dataProvider' => $dataProvider,
    'params' => $searchModel->params,
]);

$dataView->beginHeader();

if ($addUrl !== false && Yii::$app->user->can('admin.customer.contact.add')) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), $addUrl, [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'customer-form-modal',
        'data-lazy-container' => '#main-container',
    ]);
}

$dataView->endHeader();

DataView::end();

echo $this->block('@end');
