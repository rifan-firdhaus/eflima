<?php

use modules\account\web\admin\View;
use modules\crm\models\forms\customer_contact\CustomerContactSearch;
use yii\data\ActiveDataProvider;

/**
 * @var View                  $this
 * @var CustomerContactSearch $searchModel
 * @var ActiveDataProvider    $dataProvider
 */

$active = 'contact';
$this->title = Yii::t('app', 'Customer');
$this->subTitle = Yii::t('app', "Contact");
$this->menu->active = "main/crm/customer";


$this->beginContent('@modules/crm/views/admin/customer/components/index-layout.php', compact('active'));

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();


