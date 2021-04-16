<?php

use modules\account\web\admin\View;
use modules\finance\models\Tax;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View $this
 * @var Tax  $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Tax');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->name);
}

$this->icon = 'i8:tax';
$this->menu->active = 'setting/finance';

if (!$model->isNewRecord && !Lazy::isLazyModalRequest()) {
    if (Yii::$app->user->can('admin.setting.finance.tax')) {
        $this->toolbar['delete-tax'] = Html::a([
            'url' => ['/finance/admin/tax/delete', 'id' => $model->id],
            'class' => 'btn btn-outline-danger btn-icon',
            'icon' => 'i8:trash',
            'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                'object_name' => Html::tag('strong', $model->name),
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
