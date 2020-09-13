<?php

use modules\account\web\admin\View;
use modules\crm\models\forms\lead\LeadSearch;
use yii\data\ActiveDataProvider;

/**
 * @var View               $this
 * @var LeadSearch         $searchModel
 * @var ActiveDataProvider $dataProvider
 */

$active = 'contact';
$this->title = Yii::t('app', 'Lead');
$this->subTitle = Yii::t('app', "List");
$this->menu->active = "main/crm/lead";

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');


