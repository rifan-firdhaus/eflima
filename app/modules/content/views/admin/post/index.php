<?php

use modules\account\web\admin\View;
use modules\content\models\forms\post\PostSearch;
use yii\data\ActiveDataProvider;

/**
 * @var View               $this
 * @var PostSearch         $searchModel
 * @var ActiveDataProvider $dataProvider
 */

$this->title = $searchModel->type->label;
$this->menu->active = $searchModel->type->menu;
$this->subTitle = Yii::t('app', 'List');

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel', 'dataProvider'));
echo $this->block('@end');