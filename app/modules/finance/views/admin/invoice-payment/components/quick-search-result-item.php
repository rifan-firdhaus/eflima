<?php

use modules\account\web\admin\View;
use modules\finance\models\InvoicePayment;
use yii\helpers\Html;

/**
 * @var View           $this
 * @var InvoicePayment $model
 */

?>
<div class="w-50 mb-3">
    <div class="quick-search-result-item h-100 d-flex flex-column mb-0 w-auto">
        <div class="header">

            <div class="title d-block font-size-lg mb-2 d-flex justify-content-between align-items-center">
                <?= Html::a([
                    'label' => Html::encode($model->number),
                    'url' => ['/finance/admin/invoice-payment/view', 'id' => $model->id],
                    'data-lazy-container' => "#main-container",
                    'data-lazy-modal' => "invoice-view-modal",
                    'data-lazy-modal-size' => 'modal-lg',
                    'data-quick-search-close' => true,
                    'class' => 'text-monospace',
                ]); ?>
            </div>
        </div>

        <div class="content flex-grow-1 justify-content-between d-flex flex-column border-top pt-2">
            <div>
                <div class="d-flex ml-auto">
                    <div class="label w-50"><?= Yii::t('app', 'Paid at') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= Yii::$app->formatter->asDate($model->at) ?>
                    </div>
                </div>
                <div class="d-flex ml-auto">
                    <div class="label w-50"><?= Yii::t('app', 'Accepted at') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= Yii::$app->formatter->asDate($model->accepted_at) ?>
                    </div>
                </div>

                <div class="d-flex ml-auto">
                    <div class="label w-50"><?= Yii::t('app', 'Customer') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= Html::a(Html::encode($model->invoice->customer->name), ['/crm/admin/customer/view', 'id' => $model->invoice->customer_id], [
                            'data-lazy-container' => '#main-container',
                            'data-lazy-modal' => 'customer-view-modal',
                            'data-quick-search-close' => true,
                        ]) ?>
                    </div>
                </div>

                <div class="d-flex ml-auto">
                    <div class="label w-50"><?= Yii::t('app', 'Payment for') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= Html::a(Html::encode($model->invoice->number), ['/finance/admin/invoice/view', 'id' => $model->invoice->id], [
                            'data-lazy-container' => '#main-container',
                            'data-lazy-modal' => 'invoice-view-modal',
                            'data-quick-search-close' => true,
                        ]) ?>
                    </div>
                </div>
            </div>

            <div>
                <div class="d-flex ml-auto border-top mt-1 pt-1 font-weight-bold">
                    <div class="label w-50"><?= Yii::t('app', 'Amount') ?></div>
                    <div class="flex-grow-1 text-right text-success">
                        <?= Yii::$app->formatter->asCurrency($model->amount, $model->invoice->currency_code) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

