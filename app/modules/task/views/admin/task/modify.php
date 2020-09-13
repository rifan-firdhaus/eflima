<?php

use modules\account\web\admin\View;
use modules\task\assets\admin\TaskCheckListAsset;
use modules\task\models\Task;
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

if (!$model->isNewRecord) {
    $this->toolbar['delete-task'] = Html::a(
        '',
        ['/task/admin/task/delete', 'id' => $model->id],
        [
            'class' => 'btn btn-danger btn-icon',
            'icon' => 'i8:trash',
            'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                'object_name' => Html::tag('strong', $model->title),
            ]),
            'data-placement' => 'bottom',
            'title' => Yii::t('app', 'Delete'),
        ]
    );

    $this->toolbar['add-task'] = Html::a(
        Yii::t('app', 'Add'),
        ['/task/admin/task/add'],
        [
            'class' => 'btn btn-secondary',
            'icon' => 'i8:plus',
        ]
    );
}

echo $this->block('@begin');
echo $this->render('components/form', compact('model'));
echo $this->block('@end');
