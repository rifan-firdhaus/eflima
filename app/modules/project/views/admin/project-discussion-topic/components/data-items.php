<?php

use modules\account\web\admin\View;
use modules\project\models\ProjectDiscussionTopic;
use yii\data\ActiveDataProvider;

/**
 * @var View                     $this
 * @var ActiveDataProvider       $dataProvider
 * @var ProjectDiscussionTopic[] $models
 * @var int                      $currentTopicId
 */

if (!isset($currentTopicId)) {
    $currentTopicId = null;
}

$models = $dataProvider->models;

?>

<div class="project-discussion-topic-items list-group">
    <?php foreach ($models AS $model) {
        $isActive = $model->id == $currentTopicId;

        echo $this->render('data-item', compact('model', 'isActive'));
    } ?>
</div>
