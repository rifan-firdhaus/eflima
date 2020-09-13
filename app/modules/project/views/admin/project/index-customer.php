<?php

use modules\account\web\admin\View;
use modules\crm\models\Customer;
use modules\project\models\forms\project\ProjectSearch;

/**
 * @var View          $this
 * @var Customer      $customer
 * @var ProjectSearch $searchModel
 */


$this->subTitle = Yii::t('app', 'Project');

$this->beginContent('@modules/crm/views/admin/customer/components/view-layout.php', [
    'model' => $customer,
    'active' => 'project',
]);

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();
