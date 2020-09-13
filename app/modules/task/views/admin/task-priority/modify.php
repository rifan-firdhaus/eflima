<?php

use modules\account\web\admin\View;
use modules\task\models\TaskPriority;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View         $this
 * @var TaskPriority $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Task Priority');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->label);
}

$this->icon = 'i8:sorting';
$this->menu->active = 'main/task';

if (!$model->isNewRecord) {
    if (!Lazy::isLazyModalRequest()) {
        $this->toolbar['delete-task-priority'] = Html::a(
            '',
            ['/task/admin/task-priority/delete', 'id' => $model->id],
            [
                'class' => 'btn btn-danger btn-icon',
                'icon' => 'i8:trash',
                'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                    'object_name' => Html::tag('strong', $model->label),
                ]),
                'data-placement' => 'bottom',
                'title' => Yii::t('app', 'Delete'),
            ]
        );

        $this->toolbar['add-task-priority'] = Html::a(
            Yii::t('app', 'Add'),
            ['/task/admin/task-priority/add', 'id' => $model->id],
            [
                'class' => 'btn btn-secondary',
                'icon' => 'i8:plus',
            ]
        );
    }
}

echo $this->block('@begin');
echo $this->render('components/form', compact('model'));
echo $this->block('@end');
