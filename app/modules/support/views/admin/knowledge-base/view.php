<?php

use modules\account\web\admin\View;
use modules\account\widgets\StaffCommentWidget;
use modules\support\models\KnowledgeBase;
use modules\ui\widgets\Icon;
use yii\helpers\Html;

/**
 * @var View          $this
 * @var KnowledgeBase $model
 */

$this->title = $model->title;
$this->menu->active = 'main/support/knowledge_base';
$this->menu->breadcrumbs[] = ['label' => $this->title];
$this->icon = 'i8:open-book';

$this->toolbar['delete-knowledge-base'] = Html::a([
    'url' => ['/support/admin/knowledge-base/delete', 'id' => $model->id],
    'class' => 'btn btn-outline-danger btn-icon',
    'icon' => 'i8:trash',
    'data-confirmation' => Yii::t('app', 'You are about to delete {object_name}, are you sure', [
        'object_name' => Html::tag('strong', $model->title),
    ]),
    'data-placement' => 'bottom',
    'title' => Yii::t('app', 'Delete'),
    'data-toggle' => 'tooltip',
]);

$this->toolbar['update-knowledge-base'] = Html::a([
    'label' => Yii::t('app', 'Update'),
    'url' => ['/support/admin/knowledge-base/update', 'id' => $model->id],
    'class' => 'btn btn-outline-secondary',
    'icon' => 'i8:edit',
    'data-lazy-modal' => 'event-form-modal',
    'data-lazy-container' => '#main-container',
    'data-lazy-modal-size' => 'modal-lg',
]);

echo $this->block('@begin');
?>
    <div id="knowledge-base-view-wrapper-<?= $this->uniqueId; ?>" class="container-fluid knowledge-base-view-wrapper">
        <div class="row">
            <div class="col">
                <div class="knowledge-base-content my-3">
                    <?= Yii::$app->formatter->asHtml($model->content); ?>
                </div>
            </div>
        </div>
        <div class="knowledge-base-comment row bg-really-light py-3 border-top">
            <div class="col-md-12">
                <h3 class="mb-3 font-size-lg">
                    <?= Icon::show('i8:chat', ['class' => 'text-primary mr-2 icon icons8-size']) . Yii::t('app', 'Discussion') ?>
                </h3>

                <?= StaffCommentWidget::widget([
                    'relatedModel' => 'knowledge_base',
                    'relatedModelId' => $model->id,
                ]) ?>
            </div>
        </div>
    </div>
<?php
echo $this->block('@end');