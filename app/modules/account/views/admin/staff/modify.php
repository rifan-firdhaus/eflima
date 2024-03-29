<?php

use modules\account\models\Staff;
use modules\account\web\admin\View;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View  $this
 * @var Staff $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Register');
    $this->subTitle = Yii::t('app', 'Staff');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->name);
}

$this->menu->active = 'main/admin/admin';
$this->icon = 'i8:account';

if (!$model->isNewRecord && !Lazy::isLazyModalRequest()) {
    if (Yii::$app->user->can('admin.staff.delete')) {
        $this->toolbar['delete-staff'] = Html::a([
            'url' => ['/account/admin/staff/delete', 'id' => $model->id],
            'class' => 'btn btn-outline-danger btn-icon',
            'icon' => 'i8:trash',
            'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                'object_name' => $model->name,
            ]),
            'data-placement' => 'bottom',
            'title' => Yii::t('app', 'Delete'),
            'data-lazy-options' => ['method' => 'DELETE'],
        ]);
    }

    $this->toolbar['view-staff'] = Html::a([
        'label' => Yii::t('app', 'Profile'),
        'url' => ['/account/admin/staff/view', 'id' => $model->id],
        'class' => 'btn btn-outline-secondary',
        'icon' => 'i8:eye',
    ]);
}

echo $this->block('@begin');
echo $this->render('components/form', compact('model'));
echo $this->block('@end');
