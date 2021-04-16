<?php

use modules\account\web\admin\View;
use modules\finance\models\ExpenseCategory;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View            $this
 * @var ExpenseCategory $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Expense Category');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->name);
}

$this->icon = 'i8:hashtag';
$this->menu->active = 'setting/finance';

if (!$model->isNewRecord && !Lazy::isLazyModalRequest()) {
    if (Yii::$app->user->can('admin.setting.finance.expense-category.delete')) {
        $this->toolbar['delete-expense-category'] = Html::a([
            'url' => ['/finance/admin/expense-category/delete', 'id' => $model->id],
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
