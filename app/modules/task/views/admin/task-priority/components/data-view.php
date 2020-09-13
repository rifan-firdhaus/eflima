<?php


use modules\account\web\admin\View;
use modules\task\models\forms\task_priority\TaskPrioritySearch;
use modules\ui\widgets\DataView;
use modules\ui\widgets\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View               $this
 * @var TaskPrioritySearch $searchModel
 * @var array              $dataViewOptions
 */

if (!isset($dataViewOptions)) {
    $dataViewOptions = [];
}

echo $this->block('@begin', [
    'dataViewOptions' => &$dataViewOptions,
]);

$dataView = DataView::begin(ArrayHelper::merge([
    'searchModel' => $searchModel,
    'id' => 'task-priority-data-view',
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
    'searchAction' => $searchModel->searchUrl('/task/admin/task-priority/index'),
    'advanceSearchFields' => [


    ],
], $dataViewOptions));

echo $this->render('data-table', [
    'dataProvider' => $searchModel->dataProvider,
]);

$dataView->beginHeader();

echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), ['/task/admin/task-priority/add'], [
    'class' => 'btn btn-primary',
    'data-lazy-modal' => 'task-priority-form',
    'data-lazy-modal-size' => 'modal-md',
    'data-lazy-container' => '#main-container',
]);

$dataView->endHeader();

DataView::end();

echo $this->block('@begin');