<?php

use modules\account\web\admin\View;
use modules\core\components\Formatter;
use modules\task\assets\admin\TaskTimerStatisticAsset;
use modules\task\models\forms\task_timer\TaskTimerSearch;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * @var View            $this
 * @var TaskTimerSearch $searchModel
 * @var Formatter       $formatter
 */
TaskTimerStatisticAsset::register($this);

$formatter = Yii::$app->formatter;
$todayDuration = $searchModel->getTotalDurationToday();
$totalDuration = $searchModel->getTotalDuration();
$weekDuration = $searchModel->getTotalDurationThisWeek();
$statistic = Json::encode($searchModel->getStatistic());

$this->registerJs("taskTimerCart($('#task-timer-statistic-{$this->uniqueId}'),$statistic)")
?>
<div class="widgets d-flex justify-content-between border-top">
    <div class="widget w-100 d-flex flex-column justify-content-between"
         data-toggle="tooltip"
         data-placement="bottom"
         title="<?= $formatter->asDuration($totalDuration) ?>">
        <div class="widget-value"><?= $formatter->asShortDuration($totalDuration); ?></div>
        <div class="widget-label"><?= Yii::t('app', 'Total Recorded') ?></div>
    </div>

    <?php if ($searchModel->currentTask && ($estimation = $searchModel->currentTask->getEstimationSecond()) !== false): ?>
        <?php if (($late = $estimation - $totalDuration) < 0): ?>
            <div class="widget w-100 d-flex flex-column justify-content-between text-danger"
                 data-toggle="tooltip"
                 data-placement="bottom"
                 title="<?= Yii::t('app', '{duration} late', ['duration' => $formatter->asDuration(abs($late))]) ?>">
                <div class="widget-value"><?= $formatter->asShortDuration(abs($late)); ?></div>
                <div class="widget-label"><?= Yii::t('app', 'Late from estimation') ?></div>
            </div>
        <?php else: ?>
            <div class="widget w-100 d-flex flex-column justify-content-between"
                 data-toggle="tooltip"
                 data-placement="bottom"
                 title="<?= Yii::t('app', '{duration} time left based on estimation', ['duration' => $formatter->asDuration(abs($late))]) ?>">
                <div class="widget-value"><?= $formatter->asShortDuration(abs($late)); ?></div>
                <div class="widget-label"><?= Yii::t('app', 'Time left*') ?></div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="widget w-100 d-flex flex-column justify-content-between"
         data-toggle="tooltip"
         data-placement="bottom"
         title="<?= $formatter->asDuration($todayDuration) ?>">
        <div class="widget-value"><?= $formatter->asShortDuration($todayDuration); ?></div>
        <div class="widget-label"><?= Yii::t('app', 'Recorded Today') ?></div>
    </div>

    <div class="widget w-100 d-flex flex-column justify-content-between"
         data-toggle="tooltip"
         data-placement="bottom"
         title="<?= $formatter->asDuration($weekDuration) ?>">
        <div class="widget-value"><?= $formatter->asShortDuration($weekDuration); ?></div>
        <div class="widget-label"><?= Yii::t('app', 'Recorded This Week') ?></div>
    </div>

</div>

<div class="widget border-top w-100 d-flex p-0 flex-column justify-content-between">
    <?= Html::tag('div', '', ['id' => "task-timer-statistic-{$this->uniqueId}"]); ?>
</div>