<?php

use modules\account\web\admin\View;
use modules\finance\models\forms\expense\ExpenseSearch;
use modules\ui\widgets\Icon;
use yii\bootstrap4\Progress;

/**
 * @var ExpenseSearch $searchModel
 * @var View          $this
 * @var array|string  $searchAction
 * @var boolean       $withTotal
 */

if (!isset($withTotal)) {
    $withTotal = false;
}

$searchModel = clone $searchModel;

$searchModel->is_billable = '';
$searchModel->is_billed = '';
$searchModel->query = null;

$searchModel->filterQuery();

$formatter = Yii::$app->formatter;
$total = $searchModel->totalValue;
$totalBillable = $searchModel->totalBillable;
$totalNonBillable = $searchModel->totalNonBillable;
$totalBilled = $searchModel->totalBilled;
$totalNotBilled = $searchModel->totalNotBilled;

$billableRatio = $totalBillable > 0 ? round(($totalBillable / $total) * 100, 1) : 0;
$nonBillableRatio = $totalBillable > 0 ? round(($totalNonBillable / $total) * 100, 1) : 0;
$billedRatio = $totalBillable > 0 ? round(($totalBilled / $totalBillable) * 100, 1) : 0;
$billedRatio = $totalBillable > 0 ? round(($totalBilled / $totalBillable) * 100, 1) : 0;
$notBilledRatio = $totalBillable > 0 ? round(($totalNotBilled / $totalBillable) * 100, 1) : 0;
$overallBilledRatio = round($billableRatio * ($billedRatio / 100), 1);
$overallNotBilledRatio = round($billableRatio * ($notBilledRatio / 100), 1);

$inputDateFormat = Yii::$app->setting->get('date_input_format');

$allUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'is_billable' => '',
        'is_billed' => '',
    ],
]);
$billableUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'is_billable' => 1,
        'is_billed' => '',
    ],
]);
$nonBillableUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'is_billable' => 0,
        'is_billed' => '',
    ],
]);
$billedUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'is_billable' => 1,
        'is_billed' => 1,
    ],
]);
$notBilledUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'is_billable' => 1,
        'is_billed' => 0,
    ],
]);
?>
    <div class="widgets d-flex justify-content-between">

        <?php if ($withTotal): ?>
            <a href="<?= $allUrl; ?>" class="widget w-100 text-primary w-100 d-flex align-items-center" data-placement="bottom">
                <div class="widget-icon d-flex align-items-center h1 m-0 mr-2"><?= Icon::show('i8:money-transfer') ?></div>
                <div class="widget-content flex-grow-1">
                    <div class="widget-value"><?= $formatter->asCurrency($total); ?></div>
                    <div class="widget-label">
                        <?= Yii::t('app', 'Expense Total') ?>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <a href="<?= $billableUrl; ?>" class="widget w-100 text-primary w-100 d-flex align-items-center" data-placement="bottom">
            <div class="widget-icon d-flex align-items-center h1 m-0 mr-2"><?= Icon::show('i8:initiate-money-transfer') ?></div>
            <div class="widget-content flex-grow-1">
                <div class="widget-value"><?= $formatter->asCurrency($totalBillable); ?></div>
                <div class="widget-label">
                    <?= Yii::t('app', 'Billable') ?>
                    <small class="font-weight-bold">
                        (<?= $formatter->asDecimal($billableRatio) ?>%
                        <span class="font-size-xs font-weight-light"><?= Yii::t('app', 'from total') ?></span>
                        )
                    </small>
                </div>
            </div>
        </a>

        <?php if (!$withTotal): ?>
            <a href="<?= $nonBillableUrl ?>" class="widget text-warning w-100 d-flex align-items-center justify-content-between" data-placement="bottom">
                <div class="widget-icon d-flex align-items-center h1 m-0 mr-2"><?= Icon::show('i8:request-money') ?></div>
                <div class="widget-content flex-grow-1">
                    <div class="widget-value"><?= $formatter->asCurrency($totalNonBillable); ?></div>
                    <div class="widget-label">
                        <?= Yii::t('app', 'Non Billable') ?>
                        <small class="font-weight-bold">
                            (<?= $formatter->asDecimal($nonBillableRatio) ?>%
                            <span class="font-size-xs font-weight-light"><?= Yii::t('app', 'from total') ?></span>
                            )
                        </small>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <a href="<?= $billedUrl ?>" class="widget text-success w-100 d-flex align-items-center justify-content-between" data-placement="bottom">
            <div class="widget-icon d-flex align-items-center h1 m-0 mr-2"><?= Icon::show('i8:cash') ?></div>
            <div class="widget-content flex-grow-1">
                <div class="widget-value"><?= $formatter->asCurrency($totalBilled); ?></div>
                <div class="widget-label">
                    <?= Yii::t('app', 'Billed') ?>
                    <small class="font-weight-bold">
                        (<?= $formatter->asDecimal($billedRatio) ?>%
                        <span class="font-size-xs font-weight-light"><?= Yii::t('app', 'from billable') ?></span>
                        )
                    </small>
                </div>
            </div>
        </a>

        <a href="<?= $notBilledUrl ?>" class="widget w-100 text-danger d-flex align-items-center justify-content-between" data-placement="bottom">
            <div class="widget-icon d-flex align-items-center h1 m-0 mr-2"><?= Icon::show('i8:no-cash') ?></div>
            <div class="widget-content flex-grow-1">
                <div class="widget-value"><?= $formatter->asCurrency($totalNotBilled); ?></div>
                <div class="widget-label">
                    <?= Yii::t('app', 'Not Billed') ?>
                    <small class="font-weight-bold">
                        (<?= $formatter->asDecimal($notBilledRatio) ?>%
                        <span class="font-size-xs font-weight-light"><?= Yii::t('app', 'from billable') ?></span>
                        )
                    </small>
                </div>
            </div>
        </a>
    </div>

<?= Progress::widget([
    'options' => [
        'style' => 'height: 5px',
    ],
    'bars' => [
        [
            'percent' => $nonBillableRatio,
            'options' => [
                'class' => 'bg-warning',
                'data-toggle' => 'tooltip',
                'title' => Yii::t('app', 'Non Billable: {value}%', [
                    'value' => $formatter->asDecimal($nonBillableRatio),
                ]),
            ],
        ],
        [
            'percent' => $overallBilledRatio,
            'options' => [
                'class' => 'bg-success',
                'data-toggle' => 'tooltip',
                'title' => Yii::t('app', 'Billed: {value}%', [
                    'value' => $formatter->asDecimal($overallBilledRatio),
                ]),
            ],
        ],
        [
            'percent' => $overallNotBilledRatio,
            'options' => [
                'class' => 'bg-danger',
                'data-toggle' => 'tooltip',
                'title' => Yii::t('app', 'Not Billed: {value}%', [
                    'value' => $formatter->asDecimal($overallNotBilledRatio),
                ]),
            ],
        ],
    ],
]);
