<?php

use modules\account\web\admin\View;
use modules\crm\models\forms\customer\CustomerSearch;

/**
 * @var View           $this
 * @var CustomerSearch $searchModel
 */
$this->title = Yii::t('app', 'Customer');
$this->subTitle = Yii::t('app', "List");
$this->menu->active = "main/crm/customer";

$this->beginContent('@modules/crm/views/admin/customer/components/index-layout.php');

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();
