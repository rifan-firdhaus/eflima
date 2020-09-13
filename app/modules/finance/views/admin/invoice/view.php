<?php

use modules\account\web\admin\View;
use modules\account\widgets\StaffCommentWidget;
use modules\finance\models\Invoice;
use modules\ui\widgets\Icon;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var View    $this
 * @var Invoice $model
 */

$this->toolbar['delete-invoice'] = Html::a(
    '',
    ['/finance/admin/invoice/delete', 'id' => $model->id],
    [
        'class' => 'btn btn-outline-danger btn-icon',
        'icon' => 'i8:trash',
        'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
            'object_name' => Html::tag('strong', $model->number),
        ]),
        'data-placement' => 'bottom',
        'title' => Yii::t('app', 'Delete'),
    ]
);

$this->toolbar['update-invoice'] = Html::a(
    Yii::t('app', 'Update'),
    ['/finance/admin/invoice/update', 'id' => $model->id],
    [
        'class' => 'btn btn-outline-secondary',
        'data-lazy-modal' => 'invoice-form-modal',
        'data-lazy-container' => '#main-container',
        'icon' => 'i8:edit',
    ]
);
$this->toolbar['add-invoice-payment'] = Html::a(Icon::show('i8:plus') . Yii::t('app', 'Add Payment'), Url::to(['/finance/admin/invoice-payment/add', 'invoice_id' => $model->id]), [
    'class' => 'btn btn-outline-primary ' . ($model->is_paid ? 'disabled' : ''),
    'data-lazy-modal' => 'invoice-payment-form-modal',
    'data-lazy-container' => '#main-container',
    'data-lazy-modal-size' => 'modal-md',
]);

$this->beginContent('@modules/finance/views/admin/invoice/components/view-layout.php', [
    'model' => $model,
]);
echo $this->block('@begin');
?>
    <div class="d-flex h-100">
        <?php Lazy::begin([
            'id' => 'invoice-view-wrapper-lazy',
            'options' => [
                'class' => 'h-100 py-3 w-100 overflow-auto',
            ],
        ]); ?>

        <div id="invoice-view-wrapper" class="h-100">
            <div class="d-flex justify-content-between border-right bg-really-light p-3">
                <div class="invoice-view-header">
                    <h1 class="text-uppercase"><?= Yii::t('app', 'Invoice') ?></h1>
                    <div>
                        <span>#<?= Html::encode($model->number) ?></span>
                        <?= Icon::show('i8:record', ['class' => 'text-muted mx-1']) ?>
                        <span><?= Yii::$app->formatter->asDate($model->date) ?></span>
                    </div>
                    <div>
                        <?= Yii::t('app', 'Due Date: {due_date}', [
                            'due_date' => Yii::$app->formatter->asDate($model->due_date),
                        ]) ?>
                    </div>
                </div>

                <div class="grand-total">

                </div>

                <div class="invoice-view-bill-to">
                    <h4 class="mb-2"><?= Yii::t('app', 'Bill To:') ?></h4>

                    <div class="font-weight-semi-bold"><?= Html::a(Html::encode($model->customer->name), ['/crm/admin/customer/view', 'id' => $model->customer_id]) ?></div>
                    <div><?= Html::encode($model->customer->address) ?></div>
                    <div><?= Html::encode($model->customer->city) ?>, <?= Html::encode($model->customer->province) ?></div>
                    <div><?= Html::encode($model->customer->country ? $model->customer->country->name : '') ?></div>
                </div>

                <div class="invoice-view-bill-to">
                    <h4 class="mb-2"><?= Yii::t('app', 'Ship To:') ?></h4>

                    <div class="font-weight-semi-bold"><?= Html::a(Html::encode($model->customer->name), ['/crm/admin/customer/view', 'id' => $model->customer_id]) ?></div>
                    <div><?= Html::encode($model->customer->address) ?></div>
                    <div><?= Html::encode($model->customer->city) ?>, <?= Html::encode($model->customer->province) ?></div>
                    <div><?= Html::encode($model->customer->country ? $model->customer->country->name : '') ?></div>
                </div>
            </div>

            <div class="invoice-view-items">
                <?php
                echo Html::tag('div', $this->render('components/data-view-payment-statistic', compact('model')), ['class' => 'border-bottom']);
                echo $this->render('/admin/invoice-item/components/item-table', [
                    'model' => $model,
                    'hardcoded' => true,
                ]);
                ?>
            </div>

            <div class="invoice-comment bg-really-light p-3 border-top">
                <h3 class="mb-3 font-size-lg">
                    <?= Icon::show('i8:chat', ['class' => 'text-primary mr-2 icon icons8-size']) . Yii::t('app', 'Discussion') ?>
                </h3>

                <?= StaffCommentWidget::widget([
                    'relatedModel' => 'invoice',
                    'relatedModelId' => $model->id,
                ]) ?>
            </div>
        </div>

        <?php $this->registerJs("$('#invoice-view-wrapper').invoiceView()"); ?>
        <?php Lazy::end(); ?>

        <div class="border-left bg-really-light content-sidebar invoice-view-sidebar h-100 overflow-auto">
            <?= $this->render('@modules/note/views/admin/note/components/container', [
                'configurations' => [
                    'id' => 'invoice-note',
                    'model' => 'invoice',
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
