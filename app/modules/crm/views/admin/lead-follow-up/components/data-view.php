<?php

use modules\account\web\admin\View;
use modules\crm\models\forms\lead_follow_up\LeadFollowUpSearch;
use modules\ui\widgets\DataView;
use yii\helpers\ArrayHelper;

/**
 * @var View               $this
 * @var LeadFollowUpSearch $searchModel
 * @var array              $dataViewOptions
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
    'searchForm' => false,
    'clearSearchUrl' => $searchModel->clearSearchUrl(),
    'searchAction' => $searchModel->searchUrl('/crm/admin/lead-follow-up/index'),
], $dataViewOptions));

echo $this->render('data-items', [
    'dataProvider' => $dataProvider,
]);

DataView::end();

echo $this->block('@end');
