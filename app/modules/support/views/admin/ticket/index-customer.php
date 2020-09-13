<?php

use modules\account\web\admin\View;
use modules\crm\models\Customer;
use modules\support\models\forms\ticket\TicketSearch;

/**
 * @var View         $this
 * @var Customer     $customer
 * @var TicketSearch $searchModel
 */


$this->subTitle = Yii::t('app', 'Ticket');

$this->beginContent('@modules/crm/views/admin/customer/components/view-layout.php', [
    'model' => $customer,
    'active' => 'ticket',
]);

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');

$this->endContent();
