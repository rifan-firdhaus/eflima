<?php

use modules\account\web\admin\View;
use modules\task\models\TaskStatus;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View       $this
 * @var TaskStatus $model
 */

$this->title = Yii::t('app', 'Update');
$this->subTitle = Yii::t('app', 'Timer');

$this->icon = 'i8:timer';
$this->menu->active = 'main/task';

if (!$model->isNewRecord) {
    if (!Lazy::isLazyModalRequest()) {
        $this->toolbar['delete-task-timer'] = Html::a(
            '',
            ['/task/admin/task-timer/delete', 'id' => $model->id],
            [
                'class' => 'btn btn-danger btn-icon',
                'icon' => 'i8:trash',
                'data-confirmation' => Yii::t('app', 'You are about to delete {object}, are you sure', [
                    'object' => Yii::t('app', 'timer'),
                ]),
                'data-placement' => 'bottom',
                'title' => Yii::t('app', 'Delete'),
            ]
        );

        $this->toolbar['add-task-timer'] = Html::a(
            Yii::t('app', 'Add'),
            ['/task/admin/task-timer/add', 'id' => $model->id],
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
