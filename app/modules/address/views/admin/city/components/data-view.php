<?php


use modules\account\web\admin\View;
use modules\address\models\forms\city\CitySearch;
use modules\ui\widgets\DataView;
use modules\ui\widgets\Icon;
use yii\bootstrap4\ButtonGroup;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View       $this
 * @var CitySearch $searchModel
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
    'dataProvider' => $dataProvider,
    'id' => 'city-data-view',
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
    'searchAction' => $searchModel->searchUrl('/address/admin/city/index'),
    'advanceSearchFields' => [


    ],
], $dataViewOptions));

echo $this->render('data-table', compact('dataProvider'));

$dataView->beginHeader();

echo ButtonGroup::widget([
    'buttons' => [
        Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), ['/address/admin/city/add'], [
            'class' => 'btn btn-primary',
            'data-lazy-modal' => 'city-form-modal',
            'data-lazy-modal-size' => 'modal-md',
            'data-lazy-container' => '#main-container',
        ]),
    ],
]);

$dataView->endHeader();

DataView::end();

echo $this->block('@begin');
