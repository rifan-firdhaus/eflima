<?php

use modules\account\web\admin\View;
use modules\account\widgets\StaffCommentWidget;
use modules\project\models\forms\project_discussion_topic\ProjectDiscussionTopicSearch;
use modules\project\models\Project;
use modules\project\models\ProjectDiscussionTopic;
use modules\ui\widgets\Card;
use modules\ui\widgets\Icon;
use yii\helpers\Html;

/**
 * @var View                         $this
 * @var Project                      $model
 * @var ProjectDiscussionTopicSearch $searchModel
 * @var ProjectDiscussionTopic       $currentTopic
 */

$this->subTitle = Yii::t('app', 'Discussion');
$this->fullHeightContent = true;

$this->beginContent('@modules/project/views/admin/project/components/view-layout.php', [
    'model' => $model,
    'active' => 'discussion',
]);

echo $this->block('@begin');
?>
    <div class="row m-0 h-100">
        <div class="col-3 p-0">
            <?php $card = Card::begin([
                'title' => Yii::t('app', 'Topic'),
                'icon' => 'i8:ask-question',
                'options' => [
                    'class' => 'h-100 overflow-auto bg-really-light border-right',
                ],
                'headerOptions' => [
                    'class' => 'border-bottom card-header',
                ],
                'bodyOptions' => false,
            ]);

            $card->addToHeader(Html::a([
                'label' => Yii::t('app', 'Add Topic'),
                'url' => ['/project/admin/project-discussion-topic/add', 'project_id' => $model->id],
                'icon' => 'i8:plus',
                'class' => 'btn btn-primary btn-sm',
                'data-lazy-modal' => 'project-topic-discussion-form-modal',
                'data-lazy-container' => '#main-container',
                'data-lazy-modal-size' => 'modal-lg',
            ]));
            ?>

            <?= $this->render('/admin/project-discussion-topic/components/data-items', [
                'dataProvider' => $searchModel->dataProvider,
                'currentTopicId' => isset($currentTopic) ? $currentTopic->id : null
            ]) ?>

            <?php Card::end(); ?>
        </div>
        <div class="col-9 h-100 d-flex flex-column overflow-auto p-0">
            <?php if (isset($currentTopic)): ?>
                <?php
                $card = Card::begin([
                    'title' => Html::encode($currentTopic->subject),
                    'icon' => 'i8:ask-question',
                    'headerOptions' => [
                        'class' => 'border-bottom card-header',
                    ],
                ]);
                ?>

                <div class="d-flex mb-3 text-muted font-size-sm">
                    <div class="mr-3">
                        <?= Icon::show('i8:time',['class' => 'icon mr-1 icons8-size']).Yii::t('app', 'Created at: {time}', [
                            'time' => Yii::$app->formatter->asDatetime($currentTopic->created_at),
                        ]) ?>
                    </div>
                    <div>
                        <?= Icon::show('i8:chat',['class' => 'icon mr-1 icons8-size']).Yii::t('app', 'Comment: {time}', [
                            'time' => Yii::$app->formatter->asDecimal($currentTopic->totalComment),
                        ]) ?>
                    </div>
                </div>

                <?php
                echo Yii::$app->formatter->asHtml($currentTopic->content);

                Card::end();
                ?>

                <div class="invoice-comment flex-grow-1 bg-really-light p-3 border-top">
                    <?= StaffCommentWidget::widget([
                        'relatedModel' => 'project_discussion_topic',
                        'relatedModelId' => $currentTopic->id,
                    ]); ?>
                </div>

            <?php endif; ?>
        </div>
    </div>
<?php
echo $this->block('@end');

$this->endContent();