<?php


use modules\account\web\admin\View;
use modules\content\models\forms\post\PostSearch;
use yii\helpers\Html;
use modules\ui\widgets\DataView;
use modules\ui\widgets\Icon;
use yii\bootstrap4\ButtonGroup;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;


/**
 * @var View               $this
 * @var PostSearch         $searchModel
 * @var ActiveDataProvider $dataProvider
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
    'dataProvider' => $dataProvider,
    'id' => 'post-data-view',
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
    'searchAction' => ['/content/admin/post/index'],
    'advanceSearchFields' => [

    ],
], $dataViewOptions));

echo $this->render('data-table', compact('dataProvider'));

$dataView->beginHeader();

echo ButtonGroup::widget([
    'buttons' => [
        Html::a(Icon::show('i8:plus') . Yii::t('app', 'Create'), ['/content/admin/post/add', 'type' => $searchModel->type_id], [
            'class' => 'btn btn-primary',
            'data-lazy-modal' => 'post-form-modal',
            'data-lazy-container' => '#main-container',
        ]),
    ],
]);

$dataView->endHeader();

DataView::end();

echo $this->block('@begin');