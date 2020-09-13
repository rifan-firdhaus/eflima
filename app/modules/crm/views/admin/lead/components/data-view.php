<?php


use modules\account\web\admin\View;
use modules\crm\models\forms\lead\LeadSearch;
use modules\ui\widgets\DataView;
use modules\ui\widgets\Icon;
use yii\bootstrap4\ButtonGroup;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View       $this
 * @var LeadSearch $searchModel
 * @var array      $dataViewOptions
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
    'id' => 'lead-data-view',
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
    'searchAction' => $searchModel->searchUrl('/crm/admin/lead/index'),
], $dataViewOptions));

echo $this->render('data-table', [
    'dataProvider' => $dataProvider,
    'params' => $searchModel->params,
]);

$dataView->beginHeader();

echo ButtonGroup::widget([
    'buttons' => [
        Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'),
            ['/crm/admin/lead/add', 'customer_id' => (isset($searchModel->currentCustomer) ? $searchModel->currentCustomer->id : null)], [
                'class' => 'btn btn-primary',
                'data-lazy-modal' => 'lead-form-modal',
                'data-lazy-container' => '#main-container',
            ]),
    ],
]);

$dataView->endHeader();

DataView::end();

echo $this->block('@end');
