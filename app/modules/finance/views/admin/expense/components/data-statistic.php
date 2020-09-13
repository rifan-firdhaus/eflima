<?php

use modules\account\web\admin\View;
use modules\finance\models\forms\expense\ExpenseSearch;
use modules\ui\widgets\Icon;


/**
 * @var View          $this
 * @var ExpenseSearch $searchModel
 * @var array|string  $searchAction
 */

$formatter = Yii::$app->formatter;
$totalValue = $searchModel->totalValue;

$totalValueToday = $searchModel->totalValueToday;
$totalValueGrowthToday = $searchModel->totalValueGrowthToday;

$totalValueThisMonth = $searchModel->totalValueThisMonth;
$totalValueGrowthThisMonth = $searchModel->totalValueGrowthThisMonth;

$totalValueThisYear = $searchModel->totalValueThisYear;
$totalValueGrowthThisYear = $searchModel->totalValueGrowthThisYear;

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
        <div class="widget-value"><?= $formatter->asCurrency($totalValue); ?></div>
        <div class="widget-label"><?= Yii::t('app', 'Total') ?></div>
    </a>

    <a href="<?= $todayTimeUrl ?>" class="widget w-100 d-flex flex-column justify-content-between" data-placement="bottom">
        <div class="widget-value"><?= $formatter->asCurrency($totalValueToday); ?></div>
        <div class="widget-label">
            <?= Yii::t('app', 'Today') ?>
            <small class="ml-1 font-weight-bold <?= ($totalValueGrowthToday > 0 ? 'text-danger' : 'text-success') ?>">
                <?php
                if ($totalValueGrowthToday > 0) {
                    echo Icon::show('fa:caret-up', ['class' => 'mr-1']);
                } elseif ($totalValueGrowthToday < 0) {
                    echo Icon::show('fa:caret-down', ['class' => 'mr-1']);
                }

                echo $formatter->asDecimal(abs($totalValueGrowthToday) * 100)
                ?>%
            </small>
        </div>
    </a>

    <a href="<?= $thisMonthUrl ?>" class="widget w-100 d-flex flex-column justify-content-between" data-placement="bottom">
        <div class="widget-value"><?= $formatter->asCurrency($totalValueThisMonth); ?></div>
        <div class="widget-label">
            <?= Yii::t('app', 'This Month') ?>

            <small class="ml-1 font-weight-bold <?= ($totalValueGrowthThisMonth > 0 ? 'text-danger' : 'text-success') ?>">
                <?php
                if ($totalValueGrowthThisMonth > 0) {
                    echo Icon::show('fa:caret-up', ['class' => 'mr-1']);
                } elseif ($totalValueGrowthThisMonth < 0) {
                    echo Icon::show('fa:caret-down', ['class' => 'mr-1']);
                }

                echo $formatter->asDecimal(abs($totalValueGrowthThisMonth) * 100)
                ?>%
            </small>
        </div>
    </a>

    <a href="<?= $thisYearUrl ?>" class="widget w-100 d-flex flex-column justify-content-between" data-placement="bottom">
        <div class="widget-value"><?= $formatter->asCurrency($totalValueThisYear); ?></div>
        <div class="widget-label">
            <?= Yii::t('app', 'This Year') ?>

            <small class="ml-1 font-weight-bold <?= ($totalValueGrowthThisYear > 0 ? 'text-danger' : 'text-success') ?>">
                <?php
                if ($totalValueGrowthThisYear > 0) {
                    echo Icon::show('fa:caret-up', ['class' => 'mr-1']);
                } elseif ($totalValueGrowthThisYear < 0) {
                    echo Icon::show('fa:caret-down', ['class' => 'mr-1']);
                }

                echo $formatter->asDecimal(abs($totalValueGrowthThisYear) * 100)
                ?>%
            </small>
        </div>
    </a>
</div>
