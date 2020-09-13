<?php

use modules\account\web\admin\View;
use modules\support\models\TicketReply;
use yii\data\ActiveDataProvider;

/**
 * @var View               $this
 * @var ActiveDataProvider $dataProvider
 * @var TicketReply[]      $models
 */
$models = $dataProvider->models;
?>
<div class="ticket-replies-wrapper">
    <?php
    foreach ($models AS $model) {
        echo $this->render('data-list-item', compact('model'));
    }
    ?>
</div>
