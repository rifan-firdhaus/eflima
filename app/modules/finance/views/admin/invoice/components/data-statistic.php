<?php

use modules\account\web\admin\View;
use modules\finance\models\forms\invoice\InvoiceSearch;
use modules\ui\widgets\Icon;


/**
 * @var View          $this
 * @var InvoiceSearch $searchModel
 * @var array|string  $searchAction
 */

$formatter = Yii::$app->formatter;
$grandTotal = $searchModel->sumOfGrandTotal;

$grandTotalToday = $searchModel->grandTotalToday;
$grandTotalGrowthToday = $searchModel->grandTotalGrowthToday;

$grandTotalThisMonth = $searchModel->grandTotalThisMonth;
$grandTotalGrowthThisMonth = $searchModel->grandTotalGrowthThisMonth;

$grandTotalThisYear = $searchModel->grandTotalThisYear;
$grandTotalGrowthThisYear = $searchModel->grandTotalGrowthThisYear;

$time = time();
$inputDateFormat = Yii::$app->setting->get('date_input_format');
$formatter = Yii::$app->formatter;

$allTimeUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'date_from' => '',
        'date_to' => '',
    ],
]);
$todayTimeUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'date_from' => $formatter->asDate($time, $inputDateFormat),
        'date_to' => $formatter->asDate($time, $inputDateFormat),
    ],
]);
$thisMonthUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'date_from' => $formatter->asDate(strtotime(date('Y-m-01', $time)), $inputDateFormat),
        'date_to' => $formatter->asDate(strtotime(date('Y-m-t', $time)), $inputDateFormat),
    ],
]);
$thisYearUrl = $searchModel->searchUrl($searchAction, [
    $searchModel->formName() => [
        'date_from' => $formatter->asDate(strtotime(date('Y-01-01', $time)), $inputDateFormat),
        'date_to' => $formatter->asDate(strtotime(date('Y-12-31', $time)), $inputDateFormat),
    ],
]);
?>
<div class="widgets d-flex justify-content-between border-top">
    <a href="<?= $allTimeUrl ?>" class="widget w-100 d-flex flex-column justify-content-between" data-placement="bottom">
        <div class="widget-value"><?= $formatter->asCurrency($grandTotal); ?></div>
        <div class="widget-label"><?= Yii::t('app', 'Total') ?></div>
    </a>

    <a href="<?= $todayTimeUrl ?>" class="widget w-100 d-flex flex-column justify-content-between" data-placement="bottom">
        <div class="widget-value"><?= $formatter->asCurrency($grandTotalToday); ?></div>
        <div class="widget-label">
            <?= Yii::t('app', 'Today') ?>
            <small class="ml-1 font-weight-bold <?= ($grandTotalGrowthToday < 0 ? 'text-danger' : 'text-success') ?>">
                <?php
                if ($grandTotalGrowthToday > 0) {
                    echo Icon::show('fa:caret-up', ['class' => 'mr-1']);
                } elseif ($grandTotalGrowthToday < 0) {
                    echo Icon::show('fa:caret-down', ['class' => 'mr-1']);
                }

                echo $formatter->asDecimal(abs($grandTotalGrowthToday) * 100)
                ?>%
            </small>
        </div>
    </a>

    <a href="<?= $thisMonthUrl ?>" class="widget w-100 d-flex flex-column justify-content-between" data-placement="bottom">
        <div class="widget-value"><?= $formatter->asCurrency($grandTotalThisMonth); ?></div>
        <div class="widget-label">
            <?= Yii::t('app', 'This Month') ?>

            <small class="ml-1 font-weight-bold <?= ($grandTotalGrowthThisMonth < 0 ? 'text-danger' : 'text-success') ?>">
                <?php
                if ($grandTotalGrowthThisMonth > 0) {
                    echo Icon::show('fa:caret-up', ['class' => 'mr-1']);
                } elseif ($grandTotalGrowthThisMonth < 0) {
                    echo Icon::show('fa:caret-down', ['class' => 'mr-1']);
                }

                echo $formatter->asDecimal(abs($grandTotalGrowthThisMonth) * 100)
                ?>%
            </small>
        </div>
    </a>

    <a href="<?= $thisYearUrl ?>" class="widget w-100 d-flex flex-column justify-content-between" data-placement="bottom">
        <div class="widget-value"><?= $formatter->asCurrency($grandTotalThisYear); ?></div>
        <div class="widget-label">
            <?= Yii::t('app', 'This Year') ?>

            <small class="ml-1 font-weight-bold <?= ($grandTotalGrowthThisYear < 0 ? 'text-danger' : 'text-success') ?>">
                <?php
                if ($grandTotalGrowthThisYear > 0) {
                    echo Icon::show('fa:caret-up', ['class' => 'mr-1']);
                } elseif ($grandTotalGrowthThisYear < 0) {
                    echo Icon::show('fa:caret-down', ['class' => 'mr-1']);
                }

                echo $formatter->asDecimal(abs($grandTotalGrowthThisYear) * 100)
                ?>%
            </small>
        </div>
    </a>
</div>
