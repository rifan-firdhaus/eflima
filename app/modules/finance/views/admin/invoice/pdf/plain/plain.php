<<?php

use modules\finance\models\Invoice;
use yii\helpers\Html;

/**
 * @var Invoice $model
 */

$setting = Yii::$app->setting;
$items = $model->getItems()->orderBy('order')->all();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php $this->head() ?>
    </head>
    <body>
        <div class="header">
            <div class="main-header">
                <div class="header-left">
                    <div class="header-logo">
                        <?= Html::img('@web/public/img/logo.png', ['class' => 'header-logo-image']) ?>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-title"><?= Yii::t('app', 'Invoice'); ?></div>
                    <div class="header-number">#<?= Html::encode($model->number) ?></div>
                </div>
            </div>

            <div class="sub-header">
                <div class="header-left">
                    <div class="header-detail">
                        <div class="company-name"><?= Html::encode($setting->get('company/name')); ?></div>
                        <div class="address"><?= Html::encode($setting->get('company/address')) ?></div>
                        <div class="address"><?= Html::encode($setting->get('company/city')) ?>, <?= Html::encode($setting->get('company/province')) ?></div>
                    </div>
                </div>

                <div class="header-right">
                    <div class="header-detail">
                        <div class="company-name"><?= Html::encode($model->customer->name); ?></div>
                        <div class="address"><?= Html::encode($model->customer->address) ?></div>
                        <div class="address"><?= Html::encode($model->customer->city) ?>, <?= Html::encode($model->customer->province) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="body">
            <table class="table table-content">
                <thead>
                    <tr>
                        <th class="text-center fit">#</th>
                        <th>Item</th>
                        <th class="text-center fit">Qty</th>
                        <th class="text-right">Price</th>
                        <th>Tax</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items AS $key => $item): ?>
                        <tr>
                            <td class="text-center fit"><?= $key + 1; ?></td>
                            <td><?= Html::encode($item->name); ?></td>
                            <td class="text-center fit"><?= Yii::$app->formatter->asDecimal($item->amount); ?></td>
                            <td class="text-right"><?= Yii::$app->formatter->asCurrency($item->price, $model->currency_code); ?></td>
                            <td>
                                <?php foreach ($item->taxes AS $tax): ?>
                                    <div class="tax"><?= Html::encode($tax->tax->name); ?>: <?= Yii::$app->formatter->asDecimal($tax->rate) ?>%</div>
                                <?php endforeach; ?>
                            </td>
                            <td class="text-right amount">
                                <?= Yii::$app->formatter->asCurrency($item->sub_total, $model->currency_code); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <table class="table table-footer">
                <tr>
                    <th class="text-right"><?= Yii::t('app', 'Sub Total') ?></th>
                    <td class="text-right amount"><?= Yii::$app->formatter->asCurrency($model->sub_total, $model->currency_code); ?></td>
                </tr>

                <?php if ($model->tax): ?>
                    <tr>
                        <th class="text-right"><?= Yii::t('app', 'Tax') ?></th>
                        <td class="text-right amount"><?= Yii::$app->formatter->asCurrency($model->tax, $model->currency_code); ?></td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <th class="text-right"><?= Yii::t('app', 'Grand Total') ?></th>
                    <td class="text-right amount"><?= Yii::$app->formatter->asCurrency($model->grand_total, $model->currency_code); ?></td>
                </tr>
                <tr>
                    <th class="text-right"><?= Yii::t('app', 'Payment') ?></th>
                    <td class="text-right amount"><?= Yii::$app->formatter->asCurrency($model->total_paid, $model->currency_code); ?></td>
                </tr>
                <tr>
                    <th class="text-right"><?= Yii::t('app', 'Payment Due') ?></th>
                    <td class="text-right"><?= Yii::$app->formatter->asCurrency($model->total_due, $model->currency_code); ?></td>
                </tr>
            </table>
        </div>
    </body>
</html>
>
