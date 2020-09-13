<?php

use modules\account\web\admin\View;
use modules\support\models\forms\ticket\TicketSearch;

/**
 * @var View         $this
 * @var TicketSearch $searchModel
 */

$this->title = Yii::t('app', 'Ticket');
$this->subTitle = Yii::t('app', 'List');
$this->menu->active = "main/support/ticket";

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');