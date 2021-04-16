<?php


use modules\account\web\admin\View;
use modules\crm\models\forms\lead_follow_up_type\LeadFollowUpTypeSearch;
use modules\ui\widgets\DataView;
use modules\ui\widgets\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View                   $this
 * @var LeadFollowUpTypeSearch $searchModel
 * @var array                  $dataViewOptions
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
    'id' => 'lead-follow-up-type-data-view',
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
    'searchAction' => $searchModel->searchUrl('/crm/admin/lead-follow-up-type/index'),
], $dataViewOptions));

echo $this->render('data-table', compact('dataProvider'));

$dataView->beginHeader();

if (Yii::$app->user->can('admin.setting.crm.lead-follow-up-type.add')) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), ['/crm/admin/lead-follow-up-type/add'], [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'lead-follow-up-type-form-modal',
        'data-lazy-modal-size' => 'modal-md',
        'data-lazy-container' => '#main-container',
    ]);
}

$dataView->endHeader();

DataView::end();

echo $this->block('@end');
