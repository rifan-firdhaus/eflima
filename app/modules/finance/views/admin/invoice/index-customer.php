<?php

use modules\account\web\admin\View;
use modules\crm\models\Customer;
use modules\finance\models\forms\invoice\InvoiceSearch;

/**
 * @var View          $this
 * @var Customer      $customer
 * @var InvoiceSearch $searchModel
 */

$this->subTitle = Yii::t('app', 'Invoice');

$this->beginContent('@modules/crm/views/admin/customer/components/view-layout.php', [
    'model' => $customer,
    'active' => 'transaction',
]);

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();
