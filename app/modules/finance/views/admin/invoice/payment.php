<?php

use modules\account\web\admin\View;
use modules\finance\models\forms\invoice_payment\InvoicePaymentSearch;
use modules\finance\models\Invoice;
use yii\helpers\Html;

/**
 * @var View                 $this
 * @var Invoice              $model
 * @var InvoicePaymentSearch $paymentSearchModel
 */

$this->subTitle = Yii::t('app', 'Payments');

$this->beginContent('@modules/finance/views/admin/invoice/components/view-layout.php', [
    'model' => $model,
    'active' => 'payment',
]);

echo Html::tag('div', $this->render('components/data-view-payment-statistic', compact('model')), ['class' => 'border-bottom']);

echo $this->render('/admin/invoice-payment/components/data-view', [
    'searchModel' => $paymentSearchModel,
    'dataViewOptions' => [
        'searchAction' => $paymentSearchModel->searchUrl('/finance/admin/invoice/view', [
            'id' => $model->id,
            'action' => 'payment',
        ]),
    ],
    'configurations' => [
        'statistic' => false,
    ],
]);

$this->endContent();
