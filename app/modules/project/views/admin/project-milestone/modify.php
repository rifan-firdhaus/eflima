<?php

use modules\account\web\admin\View;
use modules\project\models\ProjectMilestone;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View             $this
 * @var ProjectMilestone $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Milestone');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->name);
}

$this->icon = 'i8:slider';
$this->menu->active = 'main/project';

if (!$model->isNewRecord && !Lazy::isLazyModalRequest()) {
    if (Yii::$app->user->can('admin.project.view.milestone.delete')) {
        $this->toolbar['delete-project-milestone'] = Html::a([
            'url' => ['/project/admin/project-milestone/delete', 'id' => $model->id],
            'class' => 'btn btn-outline-danger btn-icon',
            'icon' => 'i8:trash',
            'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                'object_name' => Html::tag('strong', $model->name),
            ]),
            'data-placement' => 'bottom',
            'title' => Yii::t('app', 'Delete'),
            'data-lazy-options' => ['method' => 'DELETE'],
        ]);
    }
}

echo $this->block('@begin');
echo $this->render('components/form', compact('model'));
echo $this->block('@end');
