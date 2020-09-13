<?php

use modules\account\web\admin\View;
use modules\finance\models\forms\invoice_payment\InvoicePaymentSearch;
use modules\ui\widgets\Icon;


/**
 * @var View                 $this
 * @var InvoicePaymentSearch $searchModel
 * @var array|string         $searchAction
 */

$formatter = Yii::$app->formatter;
$totalAmount = $searchModel->totalAmount;

$totalAmountToday = $searchModel->totalAmountToday;
$totalAmountGrowthToday = $searchModel->totalAmountGrowthToday;

$totalAmountThisMonth = $searchModel->totalAmountThisMonth;
$totalAmountGrowthThisMonth = $searchModel->totalAmountGrowthThisMonth;

$totalAmountThisYear = $searchModel->totalAmountThisYear;
$totalAmountGrowthThisYear = $searchModel->totalAmountGrowthThisYear;

$time = time();
$inputDateFormat = Yii::$app->setting->get('date_input_format');
$formatter = Yii::$app->formatter;

$allTimeUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'accepted_at_from' => '',
        'accepted_at_to' => '',
    ],
]);
$todayTimeUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'accepted_at_from' => $formatter->asDate($time, $inputDateFormat),
        'accepted_at_to' => $formatter->asDate($time, $inputDateFormat),
    ],
]);
$thisMonthUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'accepted_at_from' => $formatter->asDate(strtotime(date('Y-m-01', $time)), $inputDateFormat),
        'accepted_at_to' => $formatter->asDate(strtotime(date('Y-m-t', $time)), $inputDateFormat),
    ],
]);
$thisYearUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'accepted_at_from' => $formatter->asDate(strtotime(date('Y-01-01', $time)), $inputDateFormat),
        'accepted_at_to' => $formatter->asDate(strtotime(date('Y-12-31', $time)), $inputDateFormat),
    ],
]);
?>
<div class="widgets d-flex justify-content-between border-top">
    <a href="<?= $allTimeUrl ?>" class="widget w-100 d-flex flex-column justify-content-between" data-placement="bottom">
        <div class="widget-value"><?= $formatter->asCurrency($totalAmount); ?></div>
        <div class="widget-label"><?= Yii::t('app', 'Total') ?></div>
    </a>

    <a href="<?= $todayTimeUrl ?>" class="widget w-100 d-flex flex-column justify-content-between" data-placement="bottom">
        <div class="widget-value"><?= $formatter->asCurrency($totalAmountToday); ?></div>
        <div class="widget-label">
            <?= Yii::t('app', 'Today') ?>
            <small class="ml-1 font-weight-bold <?= ($totalAmountGrowthToday < 0 ? 'text-danger' : 'text-success') ?>">
                <?php
                if ($totalAmountGrowthToday > 0) {
                    echo Icon::show('fa:caret-up', ['class' => 'mr-1']);
                } elseif ($totalAmountGrowthToday < 0) {
                    echo Icon::show('fa:caret-down', ['class' => 'mr-1']);
                }

                echo $formatter->asDecimal(abs($totalAmountGrowthToday) * 100)
                ?>%
            </small>
        </div>
    </a>

    <a href="<?= $thisMonthUrl ?>" class="widget w-100 d-flex flex-column justify-content-between" data-placement="bottom">
        <div class="widget-value"><?= $formatter->asCurrency($totalAmountThisMonth); ?></div>
        <div class="widget-label">
            <?= Yii::t('app', 'This Month') ?>

            <small class="ml-1 font-weight-bold <?= ($totalAmountGrowthThisMonth < 0 ? 'text-danger' : 'text-success') ?>">
                <?php
                if ($totalAmountGrowthThisMonth > 0) {
                    echo Icon::show('fa:caret-up', ['class' => 'mr-1']);
                } elseif ($totalAmountGrowthThisMonth < 0) {
                    echo Icon::show('fa:caret-down', ['class' => 'mr-1']);
                }

                echo $formatter->asDecimal(abs($totalAmountGrowthThisMonth) * 100)
                ?>%
            </small>
        </div>
    </a>

    <a href="<?= $thisYearUrl ?>" class="widget w-100 d-flex flex-column justify-content-between" data-placement="bottom">
        <div class="widget-value"><?= $formatter->asCurrency($totalAmountThisYear); ?></div>
        <div class="widget-label">
            <?= Yii::t('app', 'This Year') ?>

            <small class="ml-1 font-weight-bold <?= ($totalAmountGrowthThisYear < 0 ? 'text-danger' : 'text-success') ?>">
                <?php
                if ($totalAmountGrowthThisYear > 0) {
                    echo Icon::show('fa:caret-up', ['class' => 'mr-1']);
                } elseif ($totalAmountGrowthThisYear < 0) {
                    echo Icon::show('fa:caret-down', ['class' => 'mr-1']);
                }

                echo $formatter->asDecimal(abs($totalAmountGrowthThisYear) * 100)
                ?>%
            </small>
        </div>
    </a>
</div>
