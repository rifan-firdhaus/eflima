<?php

use modules\account\web\admin\View;
use modules\crm\models\Customer;
use modules\crm\models\forms\customer_contact\CustomerContactSearch;

/**
 * @var Customer              $model
 * @var CustomerContactSearch $contactSearchModel
 * @var View                  $this
 */

$active = 'contact';
$this->subTitle = Yii::t('app', 'Contact');

$this->beginContent('@modules/crm/views/admin/customer/components/view-layout.php', compact('model', 'active'));

echo $this->block('@begin');
echo $this->render('../customer-contact/components/data-view', ['searchModel' => $contactSearchModel]);
echo $this->block('@end');

$this->endContent();
