<?php

use modules\account\web\admin\View;
use modules\finance\models\Invoice;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View    $this
 * @var Invoice $model
 */

$subTotal = array_sum(ArrayHelper::getColumn($model->items, 'sub_total'));
$taxes = [];
$totalTax = 0;
foreach ($model->items AS $item) {
    foreach ($item->taxes AS $tax) {
        if (!isset($taxes[$tax->tax_id])) {
            $taxes[$tax->tax_id] = [
                'total' => 0,
                'real_total' => 0,
                'tax' => $tax->tax,
            ];
        }

        $taxes[$tax->tax_id]['total'] += $tax->value;
        $taxes[$tax->tax_id]['real_total'] += $tax->real_value;
        $totalTax += $tax->value;
    }
}

$grandTotal = $subTotal + $totalTax;
$totalPaid = $model->isNewRecord ? 0 : $model->total_paid;
$totalDue = $model->isNewRecord ? $grandTotal : $model->total_due;
?>

<tr>
    <td class="text-right font-weight-semi-bold" colspan="5"><?= Yii::t('app', 'Sub Total') ?></td>
    <td class="text-right font-weight-semi-bold"><?= Yii::$app->formatter->asCurrency($subTotal, $model->currency_code) ?></td>
</tr>
<?php foreach ($taxes AS $tax): ?>
    <tr>
        <td class="text-right font-weight-semi-bold" colspan="5">
            <?= Yii::t('app', 'Tax {name} ({rate}%)', [
                'name' => Html::encode($tax['tax']->name),
                'rate' => Yii::$app->formatter->asDecimal($tax['tax']->rate),
            ]) ?>
        </td>
        <td class="text-right font-weight-semi-bold"><?= Yii::$app->formatter->asCurrency($tax['total'], $model->currency_code) ?></td>
    </tr>
<?php endforeach; ?>
<tr>
    <td class="text-right font-size-lg font-weight-semi-bold text-primary" colspan="5"><?= Yii::t('app', 'Grand Total') ?></td>
    <td class="text-right font-size-lg font-weight-semi-bold text-primary"><?= Yii::$app->formatter->asCurrency($grandTotal, $model->currency_code) ?></td>
</tr>
<tr>
    <td class="text-right font-weight-semi-bold text-success" colspan="5"><?= Yii::t('app', 'Payment') ?></td>
    <td class="text-right font-weight-semi-bold text-success"><?= Yii::$app->formatter->asCurrency($totalPaid, $model->currency_code) ?></td>
</tr>
<tr>
    <?php if ($model->is_paid): ?>
        <td class="text-right" colspan="6">
            <div class="badge badge-clean badge-success font-size-lg font-weight-semi-bold "><?= Yii::t('app', 'Fully Paid') ?></div>
        </td>
    <?php else: ?>
        <td class="text-right font-size-lg font-weight-semi-bold text-danger" colspan="5"><?= Yii::t('app', 'Payment Due') ?></td>
        <td class="text-right font-size-lg font-weight-semi-bold text-danger"><?= Yii::$app->formatter->asCurrency($totalDue, $model->currency_code) ?></td>
    <?php endif; ?>
</tr>


