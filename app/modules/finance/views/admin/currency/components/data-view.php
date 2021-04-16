<?php


use modules\account\web\admin\View;
use modules\finance\models\forms\currency\CurrencySearch;
use modules\ui\widgets\DataView;
use modules\ui\widgets\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View           $this
 * @var CurrencySearch $searchModel
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
    'id' => 'currency-data-view',
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
    'searchAction' => $searchModel->searchUrl('/finance/admin/currency/index'),
], $dataViewOptions));

echo $this->render('data-table', compact('dataProvider'));

$dataView->beginHeader();

if (Yii::$app->user->can('admin.setting.finance.currency.add')) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), ['/finance/admin/currency/add'], [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'currency-form-modal',
        'data-lazy-modal-size' => 'modal-md',
        'data-lazy-container' => '#main-container',
    ]);
}

$dataView->endHeader();

DataView::end();

echo $this->block('@end');
