<?php

use modules\account\web\admin\View;
use modules\finance\models\InvoiceItem;
use yii\helpers\Html;

/**
 * @var View        $this
 * @var InvoiceItem $model
 */

if ($model->isNewRecord && !isset($model->name)) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Invoice Item');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->name);
}

$this->icon = 'i8:shipping-container';
$this->menu->active = 'main/transaction/invoice';

echo $this->block('@begin');

$this->beginContent('@modules/finance/views/admin/invoice-item/components/modify-layout.php', [
    'model' => $model->invoice,
    'active' => 'expense'
]);

echo $this->render('components/invoice-item-form', compact('model'));

$this->endContent();

echo $this->block('@end');
