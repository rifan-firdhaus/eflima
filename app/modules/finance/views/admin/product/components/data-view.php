<?php


use modules\account\web\admin\View;
use modules\finance\models\forms\product\ProductSearch;
use modules\finance\widgets\inputs\ProductCategoryInput;
use modules\ui\widgets\DataView;
use modules\ui\widgets\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;


/**
 * @var View          $this
 * @var ProductSearch $searchModel
 * @var array         $dataViewOptions
 */

$dataProvider = $searchModel->dataProvider;

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$onSearchDateClose = new JsExpression('function(){$(this.element).closest("form").trigger("submit")}');

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'id' => 'product-data-view',
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
    'searchAction' => $searchModel->searchUrl('/finance/admin/product/index'),
    'searchFields' => [

    ],
    'advanceSearchFields' => [

    ],
], $dataViewOptions));

echo $this->block('@data-view:begin');

echo $this->render('data-table', compact('dataProvider'));

$dataView->beginHeader();

echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), ['/finance/admin/product/add'], [
    'class' => 'btn btn-primary',
    'data-lazy-modal' => 'product-form-modal',
    'data-lazy-container' => '#main-container',
    'data-lazy-modal-size' => 'sm',
]);

$dataView->endHeader();

echo $this->block('@data-view:end');

DataView::end();

echo $this->block('@end');
