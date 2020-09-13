<?php

use modules\account\web\admin\View;
use modules\finance\models\forms\invoice\InvoiceSearch;
use modules\ui\widgets\Icon;
use yii\bootstrap4\Progress;

/**
 * @var InvoiceSearch $searchModel
 * @var View          $this
 * @var array|string  $searchAction
 */

$searchModel = clone $searchModel;

$searchModel->has_payment = '';
$searchModel->has_due = '';
$searchModel->is_past_due = '';
$searchModel->query = null;

$searchModel->filterQuery();

$formatter = Yii::$app->formatter;
$grandTotal = $searchModel->sumOfGrandTotal;
$totalPaid = $searchModel->sumOfTotalPaid;
$totalDue = $searchModel->sumOfTotalDue;
$pastDue = $searchModel->sumOfTotalPastDue;

$paidRatio = $grandTotal > 0 ? round(($totalPaid / $grandTotal) * 100, 1) : 0;
$dueRatio = $grandTotal > 0 ? round(($totalDue / $grandTotal) * 100, 1) : 0;
$pastDueRatio = $grandTotal > 0 ? round(($pastDue / $grandTotal) * 100, 1) : 0;

$inputDateFormat = Yii::$app->setting->get('date_input_format');

$allUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'has_payment' => '',
        'has_due' => '',
        'is_past_due' => '',
    ],
]);
$paidUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'has_payment' => 1,
        'has_due' => '',
        'is_past_due' => '',
    ],
]);
$dueUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'has_payment' => '',
        'has_due' => 1,
        'is_past_due' => '',
    ],
]);
$pastDueUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'has_payment' => '',
        'has_due' => '',
        'is_past_due' => 1,
    ],
]);
?>
<div class="widgets d-flex justify-content-between">
    <a href="<?= $allUrl; ?>" class="widget w-100 text-primary w-100 d-flex" data-placement="bottom">
        <div class="widget-icon d-flex align-items-center h1 m-0 mr-2"><?= Icon::show('i8:billing') ?></div>
        <div class="widget-content flex-grow-1">
            <div class="widget-value"><?= $formatter->asCurrency($grandTotal); ?></div>
            <div class="widget-label">
                <?= Yii::t('app', 'Invoice Total') ?>
            </div>
        </div>
    </a>
    <a href="<?= $paidUrl ?>" class="widget w-100 d-flex text-success justify-content-between" data-placement="bottom">
        <div class="widget-icon d-flex align-items-center h1 m-0 mr-2"><?= Icon::show('i8:receive-cash') ?></div>
        <div class="widget-content flex-grow-1">
            <div class="widget-value"><?= $formatter->asCurrency($totalPaid); ?></div>
            <div class="widget-label">
                <?= Yii::t('app', 'Total Paid') ?>
                <small class="font-weight-bold">(<?= $formatter->asDecimal($paidRatio) ?>%)</small>
            </div>
        </div>
    </a>
    <a href="<?= $dueUrl ?>" class="widget <?= $totalDue === 0 ? '' : 'text-warning' ?> w-100 d-flex justify-content-between" data-placement="bottom">
        <div class="widget-icon d-flex align-items-center h1 m-0 mr-2"><?= Icon::show('i8:no-cash') ?></div>
        <div class="widget-content flex-grow-1">
            <div class="widget-value"><?= $formatter->asCurrency($totalDue); ?></div>
            <div class="widget-label">
                <?= Yii::t('app', 'Total Due') ?>
                <small class="font-weight-bold">(<?= $formatter->asDecimal($dueRatio) ?>%)</small>
            </div>
        </div>
    </a>
    <a href="<?= $pastDueUrl ?>" class="widget <?= $pastDue === 0 ? '' : 'text-danger' ?> w-100 d-flex justify-content-between" data-placement="bottom">
        <div class="widget-icon d-flex align-items-center h1 m-0 mr-2"><?= Icon::show('i8:no-cash') ?></div>
        <div class="widget-content flex-grow-1">
            <div class="widget-value"><?= $formatter->asCurrency($pastDue); ?></div>
            <div class="widget-label">
                <?= Yii::t('app', 'Past Due') ?>
                <small class="font-weight-bold">(<?= $formatter->asDecimal($pastDueRatio) ?>%)</small>
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
            'percent' => $paidRatio,
            'options' => [
                'class' => 'bg-success',
                'data-toggle' => 'tooltip',
                'title' => Yii::t('app', 'Total Paid: {value}%', [
                    'value' => Yii::$app->formatter->asDecimal($paidRatio),
                ]),
            ],
        ],
        [
            'percent' => $dueRatio - $pastDueRatio,
            'options' => [
                'class' => 'bg-warning',
                'data-toggle' => 'tooltip',
                'title' => Yii::t('app', 'Total Due: {value}%', [
                    'value' => Yii::$app->formatter->asDecimal($dueRatio - $pastDueRatio),
                ]),
            ],
        ],
        [
            'percent' => $pastDueRatio,
            'options' => [
                'class' => 'bg-danger',
                'data-toggle' => 'tooltip',
                'title' => Yii::t('app', 'Past Due: {value}%', [
                    'value' => Yii::$app->formatter->asDecimal($pastDueRatio),
                ]),
            ],
        ],
    ],
]); ?>
