<?php

use modules\account\web\admin\View;
use modules\finance\models\Invoice;
use modules\ui\widgets\Card;
use modules\ui\widgets\Icon;
use yii\helpers\Html;

/**
 * @var View    $this
 * @var Invoice $model
 * @var bool    $hardcoded
 */

if (!isset($hardcoded)) {
    $hardcoded = false;
}
?>
<?php Card::begin([
    'bodyOptions' => false,
    'icon' => 'i8:shipping-container',
    'title' => Yii::t('app', 'Invoice Items'),
]); ?>

<table class="table table-hover table-striped mb-0 invoice-item-table">
    <thead>
        <tr>
            <th width="10px"></th>
            <th><?= Yii::t('app', 'Name') ?></th>
            <th class="text-right"><?= Yii::t('app', 'Price') ?></th>
            <th class="text-right"><?= Yii::t('app', 'Amount') ?></th>
            <th class="text-right"><?= Yii::t('app', 'Tax') ?></th>
            <th class="text-right"><?= Yii::t('app', 'Total') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($hardcoded) {
            $items = $model->getItems()->orderBy(['order' => SORT_ASC])->all();

            foreach ($items AS $item) {
                echo $this->render('item-row', [
                    'model' => $item,
                ]);
            }
        }
        ?>
    </tbody>
    <tfoot>
        <?php if (Yii::$app->user->can('admin.invoice.item.add')): ?>
            <tr>
                <td colspan="6">
                    <?= Html::a(Icon::show('i8:plus') . Yii::t('app', 'Add Item'), ['/finance/admin/invoice-item/add', 'invoice_id' => $model->id], [
                        'class' => 'btn text-uppercase btn-block btn-outline-primary add-invoice-item-button',
                        'data-lazy' => 0,
                    ]) ?>
                </td>
            </tr>
        <?php endif ?>

        <?= $this->render('item-summary', compact('model')) ?>
    </tfoot>
</table>

<?php Card::end(); ?>
