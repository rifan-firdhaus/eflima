<?php

use modules\account\web\admin\View;
use modules\finance\models\Expense;
use modules\ui\widgets\Icon;
use yii\helpers\Html;

/**
 * @var View    $this
 * @var Expense $model
 */

?>
<div class="w-50 mb-3">
    <div class="quick-search-result-item h-100 d-flex flex-column mb-0 w-auto">
        <div class="header">
            <div class="title d-block font-size-lg mb-2">
                <div class="d-flex align-items-center">
                    <?php
                    if ($model->is_billable) {
                        $icon = Icon::show('i8:add-shopping-cart', [
                            'class' => 'icon icons8-size mr-2 text-success font-size-xl',
                            'data-toggle' => 'tooltip',
                            'style' => ['font-size' => '3.5rem'],
                            'title' => Yii::t('app', 'Billable'),
                        ]);
                    } else {
                        $icon = Icon::show('i8:clear-shopping-cart', [
                            'class' => 'icon icons8-size mr-2 text-danger font-size-xl',
                            'data-toggle' => 'tooltip',
                            'style' => ['font-size' => '3.5rem'],
                            'title' => Yii::t('app', 'Not Billable'),
                        ]);
                    }

                    echo $icon;
                    ?>
                    <div class="flex-grow-1">
                        <?= Html::a([
                            'label' => Html::encode($model->name),
                            'url' => [
                                '/finance/admin/expense/view',
                                'id' => $model->id,
                            ],
                            'data-lazy-container' => "#main-container",
                            'data-lazy-modal' => "expense-view-modal",
                            'data-quick-search-close' => true,
                        ]) ?>
                        <small class="text-monospace d-block">#<?= Html::encode($model->reference); ?></small>
                    </div>

                </div>
            </div>
        </div>

        <div class="content flex-grow-1 justify-content-between d-flex flex-column border-top pt-2">
            <div>
                <div class="d-flex ml-auto">
                    <div class="label w-50"><?= Yii::t('app', 'Date') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= Yii::$app->formatter->asDate($model->date) ?>
                    </div>
                </div>

                <?php if (!empty($model->customer_id)): ?>
                    <div class="d-flex ml-auto">
                        <div class="label w-50"><?= Yii::t('app', 'Customer') ?></div>
                        <div class="flex-grow-1 text-right">
                            <?= Html::a(Html::encode($model->customer->name), ['/crm/admin/customer/view', 'id' => $model->customer_id], [
                                'data-lazy-container' => '#main-container',
                                'data-lazy-modal' => 'customer-view-modal',
                                'data-quick-search-close' => true,
                            ]) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($model->invoice_item_id)): ?>
                    <div class="d-flex ml-auto">
                        <div class="label w-50"><?= Yii::t('app', 'Billed in') ?></div>
                        <div class="flex-grow-1 text-right">
                            <?= Html::a(Html::encode($model->invoiceItem->invoice->number), ['/finance/admin/invoice/view', 'id' => $model->invoiceItem->invoice->id], [
                                'data-lazy-container' => '#main-container',
                                'data-lazy-modal' => 'invoice-view-modal',
                                'data-quick-search-close' => true,
                            ]) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex ml-auto ">
                    <div class="label w-50"><?= Yii::t('app', 'Category') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= Html::encode($model->category->name) ?>
                    </div>
                </div>
            </div>

            <div>
                <div class="d-flex ml-auto border-top mt-1 pt-1">
                    <div class="label w-50"><?= Yii::t('app', 'Amount') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= Yii::$app->formatter->asCurrency($model->amount, $model->currency_code) ?>
                    </div>
                </div>

                <?php foreach ($model->taxes AS $tax): ?>
                    <div class="d-flex ml-auto">
                        <div class="label w-50">
                            <?= Yii::t('app', 'Tax {name} ({rate}%)', [
                                'name' => $tax->tax->name,
                                'rate' => Yii::$app->formatter->asDecimal($tax->rate),
                            ]) ?>
                        </div>
                        <div class="flex-grow-1 text-right">
                            <?= Yii::$app->formatter->asCurrency($tax->value, $model->currency_code) ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="d-flex ml-auto border-top mt-1 pt-1 font-weight-bold">
                    <div class="label w-50"><?= Yii::t('app', 'Grand Total') ?></div>
                    <div class="flex-grow-1 text-right">
                        <?= Yii::$app->formatter->asCurrency($model->total, $model->currency_code) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

