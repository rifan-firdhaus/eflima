<?php


use modules\account\web\admin\View;
use modules\project\models\forms\project_status\ProjectStatusSearch;
use modules\ui\widgets\DataView;
use modules\ui\widgets\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View                $this
 * @var ProjectStatusSearch $searchModel
 * @var array               $dataViewOptions
 */

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'id' => 'project-status-data-view',
    'dataProvider' => $searchModel->dataProvider,
    'linkPager' => [
        'pagination' => $searchModel->dataProvider->pagination,
    ],
    'mainSearchField' => [
        'attribute' => 'q',
    ],
    'bodyOptions' => [
        'class' => 'card-body p-0',
    ],
    'sort' => $searchModel->dataProvider->sort,
    'clearSearchUrl' => $searchModel->clearSearchUrl(),
    'searchAction' => $searchModel->searchUrl('/project/admin/project-status/index'),
], $dataViewOptions));

echo $this->render('data-table', [
    'dataProvider' => $searchModel->dataProvider,
]);

$dataView->beginHeader();

echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), ['/project/admin/project-status/add'], [
    'class' => 'btn btn-primary',
    'data-lazy-modal' => 'project-status-form',
    'data-lazy-modal-size' => 'modal-md',
    'data-lazy-container' => '#main-container',
]);

$dataView->endHeader();

DataView::end();

echo $this->block('@begin');