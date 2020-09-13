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

if (!$model->isNewRecord) {
    if (!Lazy::isLazyModalRequest()) {
        $this->toolbar['delete-expense-category'] = Html::a(
            '',
            ['/finance/admin/expense-category/delete', 'id' => $model->id],
            [
                'class' => 'btn btn-danger btn-icon',
                'icon' => 'i8:trash',
                'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
                    'object_name' => Html::tag('strong', $model->name),
                ]),
                'data-placement' => 'bottom',
                'title' => Yii::t('app', 'Delete'),
            ]
        );

        $this->toolbar['add-expense-category'] = Html::a(
            Yii::t('app', 'Add'),
            ['/finance/admin/expense-category/add', 'id' => $model->id],
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
