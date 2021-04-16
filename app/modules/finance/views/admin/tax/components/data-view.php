<?php


use modules\account\web\admin\View;
use modules\finance\models\forms\tax\TaxSearch;
use modules\ui\widgets\DataView;
use modules\ui\widgets\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View      $this
 * @var TaxSearch $searchModel
 * @var array     $dataViewOptions
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
    'id' => 'tax-data-view',
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
    'searchAction' => $searchModel->searchUrl('/finance/admin/tax/index'),
], $dataViewOptions));

echo $this->render('data-table', compact('dataProvider'));

$dataView->beginHeader();

if(Yii::$app->user->can('admin.setting.finance.tax.add')) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), ['/finance/admin/tax/add'], [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'tax-form-modal',
        'data-lazy-modal-size' => 'modal-md',
        'data-lazy-container' => '#main-container',
    ]);
}

$dataView->endHeader();

DataView::end();

echo $this->block('@begin');
