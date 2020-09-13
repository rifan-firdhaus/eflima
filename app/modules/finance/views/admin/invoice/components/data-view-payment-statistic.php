<?php

use modules\account\web\admin\View;
use modules\finance\models\Invoice;
use modules\ui\widgets\Icon;
use yii\helpers\Url;

/**
 * @var Invoice $model
 * @var View    $this
 */

$formatter = Yii::$app->formatter;
$paidRatio = round(($model->total_paid / $model->grand_total) * 100, 1);
$dueRatio = round(($model->total_due / $model->grand_total) * 100, 1);
?>
<div class="widgets d-flex justify-content-between">
    <a href="<?= Url::to(['/finance/admin/invoice/view', 'id' => $model->id]) ?>" class="widget w-100 d-flex" data-placement="bottom">
        <div class="widget-icon d-flex align-items-center h1 m-0 mr-2"><?= Icon::show('i8:billing') ?></div>
        <div class="widget-content flex-grow-1">
            <div class="widget-value"><?= $formatter->asCurrency($model->grand_total, $model->currency_code); ?></div>
            <div class="widget-label">
                <?= Yii::t('app', 'Invoice Total') ?>
            </div>
        </div>
    </a>
    <a href="<?= Url::to(['/finance/admin/invoice/view', 'id' => $model->id, 'action' => 'payment']) ?>" class="widget w-100 d-flex justify-content-between" data-placement="bottom">
        <div class="widget-icon d-flex align-items-center h1 m-0 mr-2"><?= Icon::show('i8:receive-cash') ?></div>
        <div class="widget-content flex-grow-1">
            <div class="widget-value"><?= $formatter->asCurrency($model->total_paid, $model->currency_code); ?></div>
            <div class="widget-label">
                <?= Yii::t('app', 'Total Paid') ?>
                <small class="font-weight-bold">(<?= $formatter->asDecimal($paidRatio) ?>%)</small>
            </div>
        </div>
    </a>
    <a href="#" class="widget <?= $model->is_paid ? '' : 'text-danger' ?> w-100 d-flex justify-content-between" data-placement="bottom">
        <div class="widget-icon d-flex align-items-center h1 m-0 mr-2"><?= Icon::show('i8:no-cash') ?></div>
        <div class="widget-content flex-grow-1">
            <div class="widget-value"><?= $formatter->asCurrency($model->total_due, $model->currency_code); ?></div>
            <div class="widget-label">
                <?= Yii::t('app', 'Total Due') ?>
                <small class="font-weight-bold">(<?= $formatter->asDecimal($dueRatio) ?>%)</small>
            </div>
        </div>
    </a>
</div>
