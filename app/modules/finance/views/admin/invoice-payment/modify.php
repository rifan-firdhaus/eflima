<?php

use modules\account\web\admin\View;
use modules\finance\models\InvoicePayment;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View           $this
 * @var InvoicePayment $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Payment');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->number);
}

$this->icon = 'i8:hashtag';
$this->menu->active = 'transaction/payment';

echo $this->block('@begin');
echo $this->render('components/form', compact('model'));
echo $this->block('@end');
