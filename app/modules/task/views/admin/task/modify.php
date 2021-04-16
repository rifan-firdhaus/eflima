<?php

use modules\account\web\admin\View;
use modules\task\assets\admin\TaskCheckListAsset;
use modules\task\models\Task;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View $this
 * @var Task $model
 */

TaskCheckListAsset::register($this);

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Task');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->title);
}

$this->icon = 'i8:checked';

$this->menu->active = 'main/task';

if (!$model->isNewRecord && !Lazy::isLazyModalRequest()) {
    if (Yii::$app->user->can('admin.task.delete')) {
        $this->toolbar['delete-task'] = Html::a([
            'url' => ['/task/admin/task/delete', 'id' => $model->id],
            'class' => 'btn btn-outline-danger btn-icon',
            'icon' => 'i8:trash',
            'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                'object_name' => Html::tag('strong', $model->title),
            ]),
            'data-placement' => 'bottom',
            'title' => Yii::t('app', 'Delete'),
            'data-lazy-options' => ['method' => 'DELETE']
        ]);
    }
}

echo $this->block('@begin');
echo $this->render('components/form', compact('model'));
echo $this->block('@end');
