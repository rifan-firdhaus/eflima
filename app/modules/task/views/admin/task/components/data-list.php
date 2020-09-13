<?php

use modules\account\web\admin\View;
use modules\task\models\TaskPriority;
use modules\task\models\TaskStatus;
use yii\data\ActiveDataProvider;

/**
 * @var View               $this
 * @var ActiveDataProvider $dataProvider
 */
$statusModels = TaskStatus::find()->enabled()->createCommand()->queryAll();
$priorityModels = TaskPriority::find()->enabled()->createCommand()->queryAll();
?>

<div class="task-list">
    <?php foreach ($dataProvider->models AS $model): ?>
        <?= $this->render('data-list-item', compact('model', 'statusModels', 'priorityModels')) ?>
    <?php endforeach; ?>
</div>
