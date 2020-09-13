<?php

use modules\account\web\admin\View;
use modules\finance\models\forms\invoice\InvoiceSearch;

/**
 * @var View          $this
 * @var InvoiceSearch $searchModel
 */
$this->title = Yii::t('app', 'Payment');
$this->subTitle = Yii::t('app', 'List');
$this->menu->active = "main/transaction/payment";

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');