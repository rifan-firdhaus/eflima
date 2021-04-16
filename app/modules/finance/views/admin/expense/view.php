<?php

use modules\account\web\admin\View;
use modules\account\widgets\StaffCommentWidget;
use modules\core\helpers\Common;
use modules\finance\models\Expense;
use modules\ui\widgets\Icon;
use yii\helpers\Html;

/**
 * @var View    $this
 * @var Expense $model
 */

$this->beginContent('@modules/finance/views/admin/expense/components/view-layout.php', [
    'model' => $model,
]);

echo $this->block('@begin');

if (Yii::$app->user->can('admin.expense.delete')) {
    $this->toolbar['delete-expense'] = Html::a([
        'url' => ['/finance/admin/expense/delete', 'id' => $model->id],
        'class' => 'btn btn-outline-danger btn-icon',
        'icon' => 'i8:trash',
        'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure?', [
            'object_name' => Html::tag('strong', $model->name),
        ]),
        'data-placement' => 'bottom',
        'title' => Yii::t('app', 'Delete'),
        'data-toggle' => 'tooltip',
        'data-lazy-options' => ['method' => 'DELETE'],
    ]);
}


if ($model->is_billable && !$model->isBilled && Yii::$app->user->can('admin.expense.bill')) {
    $this->toolbar['add-expense-to-invoice'] = Html::a(Icon::show('i8:transaction-2') . Yii::t('app', 'Add to Invoice'), ['/finance/admin/expense/add-to-invoice','id' => $model->id], [
        'title' => Yii::t('app', 'Add to Invoice'),
        'class' => 'btn btn-outline-primary',
        'data-lazy-container' => '#main-container',
        'data-lazy-modal' => 'add-to-invoice-form-modal',
        'data-lazy-modal-size' => 'modal-md',
        'data-toggle' => 'tooltip',
    ]);
}

if (Yii::$app->user->can('admin.expense.update')) {
    $this->toolbar['update-expense'] = Html::a([
        'label' => Html::tag('span', Yii::t('app', 'Update'), ['class' => 'btn-label']),
        'url' => ['/finance/admin/expense/update', 'id' => $model->id],
        'class' => 'btn btn-icon-sm btn-outline-secondary',
        'icon' => 'i8:edit',
        'data-lazy-modal' => 'expense-form-modal',
        'data-lazy-container' => '#main-container',
    ]);
}

if ($model->is_billable && empty($model->invoice_item_id) && Yii::$app->user->can('bill')) {
    $this->toolbar['add-expense-to-invoice'] = Html::a([
        'label' => Yii::t('app', 'Add to Invoice'),
        'url' => ['/finance/admin/expense/add-to-invoice', 'id' => $model->id],
        'data-lazy-container' => '#main-container',
        'data-lazy-modal' => 'add-to-invoice-form-modal',
        'data-lazy-modal-size' => 'modal-md',
        'class' => 'btn btn-outline-primary',
        'icon' => 'i8:transaction-2',
    ]);
}

?>
    <div class="d-flex h-100">
        <div id="expense-view-wrapper-<?= $this->uniqueId; ?>" class="expense-view-wrapper h-100 w-100 overflow-auto container-fluid">
            <div class="row h-100">
                <div class="col-md-6 pt-3 h-100 overflow-auto">
                    <table class="table table-detail-view">

                        <tr>
                            <th class="border-top-0 text-nowrap"><?= Yii::t('app', 'Expense Name') ?></th>
                            <td class="border-top-0"><?= Html::encode($model->name) ?></td>
                        </tr>

                        <tr>
                            <th class="text-nowrap"><?= Yii::t('app', 'Date') ?></th>
                            <td>
                                <?= Yii::$app->formatter->asDatetime($model->date) ?>
                                <div class="font-size-sm">
                                    <?= Yii::$app->formatter->asRelativeTime($model->date) ?>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th class="text-nowrap"><?= Yii::t('app', 'Category') ?></th>
                            <td>
                                <?= Html::a([
                                    'label' => Html::encode($model->category->name),
                                    'url' => ['/finance/admin/expense-category/update', 'id' => $model->category_id],
                                    'data-lazy-modal' => 'expense-category-view-modal',
                                    'data-lazy-modal-size' => 'modal-md',
                                    'data-lazy-container' => '#main-container',
                                    'class' => 'd-block',
                                ]) ?>
                            </td>
                        </tr>

                        <?php if (!Common::isEmpty($model->customer_id)): ?>
                            <tr>
                                <th class="text-nowrap"><?= Yii::t('app', 'Customer') ?></th>
                                <td>
                                    <?= Html::a([
                                        'label' => Html::encode($model->customer->name),
                                        'url' => ['/crm/admin/customer/view', 'id' => $model->customer_id],
                                        'data-lazy-modal' => 'customer-view-modal',
                                        'data-lazy-container' => '#main-container',
                                        'class' => 'd-block',
                                    ]) ?>
                                    <div class="font-size-sm">
                                        <?= Html::a([
                                            'label' => Html::encode($model->customer->primaryContact->name),
                                            'url' => ['/crm/admin/customer/view', 'id' => $model->customer->primaryContact->id],
                                            'data-lazy-modal' => 'customer-contact-form-modal',
                                            'data-lazy-container' => '#main-container',
                                            'class' => 'd-block text-muted',
                                        ]) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <th><?= Yii::t('app', 'Billable') ?></th>
                            <td>
                                <?php
                                if ($model->is_billable) {
                                    $icon = Icon::show('i8:add-shopping-cart', [
                                        'class' => 'icon icons8-size mr-1 text-success',
                                        'data-toggle' => 'tooltip',
                                        'title' => Yii::t('app', 'Billable'),
                                    ]);
                                    $billable = Html::tag('div', $icon . Yii::t('app', 'Billable'));

                                    if (empty($model->invoice_item_id)) {
                                        $label = Html::tag('div', Yii::t('app', 'Not Billed Yet'), [
                                            'class' => 'text-danger data-table-secondary-text',
                                        ]);
                                    } else {
                                        $invoiceLink = Html::a(Html::encode($model->invoiceItem->invoice->number), ['/finance/admin/invoice/view', 'id' => $model->invoiceItem->invoice_id], [
                                            'data-lazy-container' => '#main-container',
                                            'data-lazy-modal' => 'invoice-view-modal',
                                        ]);
                                        $label = Html::tag('div', Yii::t('app', 'Billed in: {invoice}', ['invoice' => $invoiceLink]), [
                                            'class' => 'data-table-secondary-text',
                                        ]);
                                    }

                                    echo $billable . $label;
                                } else {
                                    $icon = Icon::show('i8:clear-shopping-cart', [
                                        'class' => 'icon icons8-size mr-1 text-danger',
                                        'data-toggle' => 'tooltip',
                                        'title' => Yii::t('app', 'Not Billable'),
                                    ]);

                                    echo Html::tag('div', $icon . Yii::t('app', 'Not Billable'));
                                }
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <th class="text-nowrap"><?= Yii::t('app', 'Reference') ?></th>
                            <td><?= Html::encode($model->reference) ?></td>
                        </tr>

                        <?php if (!Common::isEmpty($model->description)): ?>
                            <tr>
                                <th class="text-nowrap"><?= Yii::t('app', 'Description') ?></th>
                                <td>
                                    <?= Yii::$app->formatter->asHtml($model->description) ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <th><?= Yii::t('app', 'Created at') ?></th>
                            <td>
                                <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
                                <div class="font-size-sm">
                                    <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>
                                </div>
                            </td>
                        </tr>

                    </table>

                    <div class="bg-soft-warning mb-3 p-3 rounded-lg border-lg overflow-hidden w-100">
                        <table class="table mb-0 table-detail-view">
                            <?php if (!empty($model->taxes)): ?>
                                <tr>
                                    <th><?= Yii::t('app', 'Total Before Tax') ?></th>
                                    <td class="text-right text-primary">
                                        <?= Yii::$app->formatter->asCurrency($model->amount, $model->currency_code) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>


                            <?php foreach ($model->taxes AS $tax): ?>
                                <tr>
                                    <th>
                                        <?= Yii::t('app', 'Tax {name} ({rate}%)', [
                                            'name' => Html::encode($tax->tax->name),
                                            'rate' => Yii::$app->formatter->asDecimal($tax->rate),
                                        ]) ?>
                                    </th>
                                    <td class="text-right text-primary">
                                        <?= Yii::$app->formatter->asCurrency($tax->value, $model->currency_code) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <tr>
                                <th class="font-size-lg"><?= empty($model->taxes) ? Yii::t('app', 'Total') : Yii::t('app', 'Total After Tax') ?></th>
                                <td class="font-size-lg text-right text-primary">
                                    <?= Yii::$app->formatter->asCurrency($model->total, $model->currency_code) ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6 h-100 overflow-auto bg-really-light border-left py-3">
                    <div class="expense-comment">
                        <h3 class="mb-3 font-size-lg">
                            <?= Icon::show('i8:chat', ['class' => 'text-primary mr-2 icon icons8-size']) . Yii::t('app', 'Discussion') ?>
                        </h3>

                        <?= StaffCommentWidget::widget([
                            'relatedModel' => 'expense',
                            'relatedModelId' => $model->id,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-left bg-really-light content-sidebar expense-view-sidebar h-100 overflow-auto">
            <?= $this->render('@modules/note/views/admin/note/components/container', [
                'configurations' => [
                    'id' => 'expense-note',
                    'model' => 'expense',
                    'model_id' => $model->id,
                    'inline' => true,
                    'search' => false,
                    'jsOptions' => [
                        'autoLoad' => true,
                    ],
                ],
            ]) ?>
        </div>
    </div>
<?php
echo $this->block('@end');

$this->endContent();
