<?php

use modules\account\web\admin\View;
use modules\support\models\KnowledgeBase;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View          $this
 * @var KnowledgeBase $model
 */

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Add');
    $this->subTitle = Yii::t('app', 'Knowledge Base');
} else {
    $this->title = Yii::t('app', 'Update');
    $this->subTitle = Html::encode($model->title);
}

$this->icon = 'i8:open-book';
$this->menu->active = 'main/support/knowledge-base';

if (!$model->isNewRecord) {
    if (!Lazy::isLazyModalRequest()) {
        $this->toolbar['delete-knowledge-base'] = Html::a(
            '',
            ['/task/admin/knowledge-base/delete', 'id' => $model->id],
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

        $this->toolbar['add-knowledge-base'] = Html::a(
            Yii::t('app', 'Add'),
            ['/task/admin/knowledge-base/add', 'id' => $model->id],
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
