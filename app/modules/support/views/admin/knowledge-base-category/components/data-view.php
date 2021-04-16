<?php


use modules\account\web\admin\View;
use modules\support\models\forms\knowledge_base_category\KnowledgeBaseCategorySearch;
use modules\ui\widgets\DataView;
use modules\ui\widgets\Icon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * @var View                        $this
 * @var KnowledgeBaseCategorySearch $searchModel
 * @var array                       $dataViewOptions
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
    'id' => 'knowledge-base-category-data-view',
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
    'searchAction' => $searchModel->searchUrl('/support/admin/knowledge-base-category/index'),
], $dataViewOptions));

echo $this->render('data-table', compact('dataProvider'));

$dataView->beginHeader();

If (Yii::$app->user->can('admin.knowledge-base.category.add')) {
    echo Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), ['/support/admin/knowledge-base-category/add'], [
        'class' => 'btn btn-primary',
        'data-lazy-modal' => 'knowledge-base-category-form',
        'data-lazy-modal-size' => 'modal-md',
        'data-lazy-container' => '#main-container',
    ]);
}

$dataView->endHeader();

DataView::end();

echo $this->block('@begin');
