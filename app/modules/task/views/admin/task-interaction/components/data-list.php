<?php

use modules\account\web\admin\View;
use modules\task\models\TaskInteraction;
use yii\data\ActiveDataProvider;

/**
 * @var View               $this
 * @var ActiveDataProvider $dataProvider
 * @var TaskInteraction[]  $models
 */
$models = $dataProvider->models;
?>
<div class="task-interaction-list">
    <?php
    foreach ($models AS $model) {
        echo $this->render('data-list-item', compact('model'));
    }
    ?>
</div>
