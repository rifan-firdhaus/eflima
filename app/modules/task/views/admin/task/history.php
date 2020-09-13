<?php

use modules\account\models\forms\history\HistorySearch;
use modules\account\web\admin\View;
use modules\account\widgets\history\HistoryWidget;
use modules\task\models\Task;

/**
 * @var View          $this
 * @var Task          $model
 * @var HistorySearch $historySearchModel
 */

$active = 'history'; // Set history tab to active

$this->title = $model->title;
$this->subTitle = Yii::t('app', 'History');
$this->menu->active = "main/task";
?>

<?php $this->beginContent('@modules/task/views/admin/task/components/view-layout.php', compact('model', 'active')); ?>

    <div class="row m-0">
        <div class="col-md-12 py-3">
            <?= HistoryWidget::widget([
                'id' => 'task-history',
                'dataProvider' => $historySearchModel->dataProvider,
            ]); ?>
        </div>
    </div>

<?php $this->endContent();
