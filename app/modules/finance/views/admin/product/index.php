<?php

use modules\account\web\admin\View;
use modules\finance\models\forms\product\ProductSearch;

/**
 * @var View          $this
 * @var ProductSearch $searchModel
 */

$this->title = Yii::t('app', 'Product');
$this->subTitle = Yii::t('app', 'List');
$this->menu->active = "main/transaction/product";

echo $this->block('@begin');
echo $this->render('components/data-view', compact('searchModel'));
echo $this->block('@end');