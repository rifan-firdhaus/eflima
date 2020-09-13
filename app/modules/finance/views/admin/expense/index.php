<?php

use modules\account\web\admin\View;
use modules\finance\models\forms\expense\ExpenseSearch;

/**
 * @var View          $this
 * @var ExpenseSearch $searchModel
 */

$this->title = Yii::t('app', 'Expense');
$this->subTitle = Yii::t('app', 'List');
$this->menu->active = "main/transaction/expense";

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');