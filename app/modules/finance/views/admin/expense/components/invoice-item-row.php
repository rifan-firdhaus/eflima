<?php

use modules\account\web\admin\View;
use modules\finance\models\InvoiceItem;
use modules\ui\widgets\Icon;
use yii\helpers\Html;

/**
 * @var View        $this
 * @var InvoiceItem $model
 */

?>
<tr data-id="<?= $model->id ?>">
    <td class="align-middle text-center text-nowrap">
        <?= Html::a(Icon::show('i8:edit'), ['/finance/admin/expense/update-invoice-item', 'id' => $model->id], [
            'class' => 'text-primary align-middle update-invoice-item-button m-0 h4',
            'data-lazy' => 0,
        ]) ?>
        <?= Html::a(Icon::show('i8:trash'), ['/finance/admin/invoice-item/delete', 'id' => $model->id], [
            'class' => 'text-danger align-middle delete-invoice-item-button m-0 h4',
            'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                'object_name' => Yii::t('app', 'this item'),
            ]),
            'title' => Yii::t('app','Delete'),
            'data-lazy-container' => false,
        ]) ?>
    </td>
    <td class="align-middle">
        <div class="d-flex w-100">
            <?= Html::a(Html::encode($model->name), ['/finance/admin/expense/update-invoice-item', 'id' => $model->id], [
                'class' => 'update-invoice-item-button flex-grow w-100',
                'data-lazy' => 0,
            ]) ?>
            <div class="text-right">
                <?= Html::a([
                    'label' => Yii::t('app', 'Expense') . Icon::show('i8:external-link', ['class' => 'icon ml-2']),
                    'url' => [
                        '/finance/admin/expense/view',
                        'id' => $model->params['expense_id'],
                    ],
                    'class' => 'badge px-2 py-1 text-uppercase badge-info',
                    'data-lazy-modal' => 'expense-form-modal',
                    'data-lazy-container' => '#main-container',
                ]) ?>
            </div>
        </div>
    </td>
    <td class="text-right align-middle">
        <?= Yii::$app->formatter->asCurrency($model->price, $model->invoice->currency_code) ?>
    </td>
    <td class="text-right align-middle"><?= Yii::$app->formatter->asDecimal($model->amount) ?></td>
    <td class="text-right align-middle">
        <?php
        $taxes = [];

        foreach ($model->taxes AS $tax) {
            $taxes[] = Html::tag('span', Html::encode($tax->tax->name), [
                'data-toggle' => 'tooltip',
                'title' => Yii::$app->formatter->asDecimal($tax->rate) . '%',
            ]);
        };

        echo implode(', ', $taxes);
        ?>
    </td>
    <td class="text-right align-middle"><?= Yii::$app->formatter->asCurrency($model->sub_total, $model->invoice->currency_code) ?></td>
</tr>
