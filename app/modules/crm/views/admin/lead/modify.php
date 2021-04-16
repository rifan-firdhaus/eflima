<?php

use modules\account\web\admin\View;
use modules\crm\models\Lead;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View $this
 * @var Lead $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Contact');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->name);
}

$this->icon = 'i8:connect';
$this->menu->active = 'main/crm/lead';

if (!$model->isNewRecord) {
    if (!Lazy::isLazyModalRequest()) {
        $this->toolbar['delete-lead'] = Html::a([
            'url' => ['/crm/admin/lead/delete', 'id' => $model->id],
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
