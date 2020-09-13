<?php

use modules\account\models\forms\staff\StaffSearch;
use modules\account\web\admin\View;
use yii\data\ActiveDataProvider;

/**
 * @var View               $this
 * @var StaffSearch        $searchModel
 */

$this->title = Yii::t('app', 'Staff');
$this->menu->active = 'main/admin/admin';
$this->subTitle = Yii::t('app', 'List');

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');
