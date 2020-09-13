<?php

use modules\account\models\forms\staff\StaffAccountSearch;
use modules\account\web\admin\View;
use yii\data\ActiveDataProvider;

/**
 * @var View                       $this
 * @var StaffAccountSearch $searchModel
 * @var ActiveDataProvider         $dataProvider
 */

$this->title = Yii::t('app', 'Staff Account');
$this->menu->active = 'main/admin/admin';

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel', 'dataProvider'));
echo $this->block('@end');