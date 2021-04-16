<?php

use modules\account\web\admin\View;
use modules\crm\models\forms\lead\LeadSearch;
use yii\data\ActiveDataProvider;

/**
 * @var View               $this
 * @var LeadSearch         $searchModel
 * @var ActiveDataProvider $dataProvider
 */

$active = 'index';
$this->subTitle = Yii::t('app', "List");

$this->beginContent('@modules/crm/views/admin/lead/components/index-layout.php',compact('active'));

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();

