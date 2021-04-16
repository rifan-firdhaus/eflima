<?php

use modules\account\web\admin\View;
use modules\finance\models\Invoice;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View    $this
 * @var Invoice $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Invoice');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->number);
}

$this->icon = 'i8:money-transfer';
$this->menu->active = 'main/transaction/invoice';

if (!$model->isNewRecord && !Lazy::isLazyModalRequest()) {
    if (Yii::$app->user->can('admin.invoice.delete')) {
        $this->toolbar['delete-invoice'] = Html::a([
            'label' => ['/finance/admin/invoice/delete', 'id' => $model->id],
            'class' => 'btn btn-outline-danger btn-icon',
            'icon' => 'i8:trash',
            'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                'object_name' => Html::tag('strong', $model->number),
            ]),
            'data-placement' => 'bottom',
            'title' => Yii::t('app', 'Delete'),
        ]);
    }

    if (Yii::$app->user->can('admin.invoice.view')) {
        $this->toolbar['view-invoice'] = Html::a([
            'label' => Yii::t('app', 'View'),
            'url' => ['/finance/admin/invoice/view', 'id' => $model->id],
            'class' => 'btn btn-outline-secondary',
            'data-lazy-modal' => 'invoice-view-modal',
            'data-lazy-container' => '#main-container',
            'icon' => 'i8:eye',
        ]);
    }
}

echo $this->block('@begin');
echo $this->render('components/form', compact('model'));
echo $this->block('@end');
