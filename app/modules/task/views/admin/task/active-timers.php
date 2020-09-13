<?php

use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\task\models\Task;
use modules\task\models\TaskTimer;
use modules\task\widgets\inputs\TaskStatusDropdown;
use modules\ui\widgets\CountdownWidget;
use modules\ui\widgets\Icon;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/**
 * @var View               $this
 * @var ActiveDataProvider $dataProvider
 * @var Task[]             $models
 * @var StaffAccount       $account
 */

$account = Yii::$app->user->identity;
$models = $dataProvider->models;
$this->title = Yii::t('app', 'Active Timer');

$this->beginContent('@modules/account/views/layouts/admin/components/side-panel-layout.php');
?>
<?php if (empty($models)): ?>
    <?php
    $icon = Icon::show('i8:timer');
    $text = Html::tag('div', Yii::t('app', 'No timer to show'), [
        'class' => 'text',
    ]);

    echo Html::tag('div', $icon . $text, [
        'class' => 'empty ',
    ]);
    ?>
<?php else: ?>
    <?php foreach ($models AS $model): ?>
        <?php
        /** @var TaskTimer $activeTimer */
        $activeTimer = $model->getActiveTimer($account->profile->id);
        ?>


        <div class="task-timer-item">

            <div class="title">
                <?= Html::a(Html::encode($model->title), ['/task/admin/task/view', 'id' => $model->id], [
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'task-view-modal',
                    'class' => 'side-panel-close',
                ]) ?>
            </div>

            <div class="task-item-timer mb-2">
                <?= CountdownWidget::widget([
                    'since' => $activeTimer->started_at - time(),
                ]);
                ?>
            </div>

            <?= TaskStatusDropdown::widget([
                'value' => $model->status_id,
                'options' => [
                    'class' => 'w-100 mb-2',
                ],
                'buttonOptions' => [
                    'class' => 'w-100',
                ],
                'url' => function ($status) use ($model) {
                    return ['/task/admin/task/change-status', 'id' => $model->id, 'status' => $status['id']];
                },
            ]); ?>

            <div class="task-item-action d-flex">
                <?= Html::a(Yii::t('app', 'Detail'), ['/task/admin/task/view', 'id' => $model->id, 'action' => 'timer'], [
                    'class' => 'btn btn-sm side-panel-close btn-outline-primary w-100 mr-1',
                    'icon' => 'i8:timer',
                    'data-lazy-container' => '#main-container',
                    'data-lazy-modal' => 'task-view-modal',
                ]) ?>
                <?= Html::a(Yii::t('app', 'Stop'), ['/task/admin/task/toggle-timer', 'id' => $model->id, 'start' => 0], [
                    'class' => 'btn btn-sm btn-outline-danger w-100',
                    'icon' => 'i8:stop',
                ]) ?>
            </div>
        </div>


    <?php
    endforeach;
endif;
$this->endContent();
