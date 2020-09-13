<?php

use modules\account\web\admin\View;
use modules\file_manager\helpers\ImageVersion;
use modules\task\models\TaskInteraction;
use yii\bootstrap4\Progress;
use yii\helpers\Html;

/**
 * @var View            $this
 * @var TaskInteraction $model
 */

echo $this->block('@begin');
?>

    <div class="task-interaction-item d-flex <?= ($model->isMe ? 'task-interaction-item-me' : '') ?>">
        <div class="task-interaction-avatar flex-shrink-0"><?= Html::img($model->staff->account->getFileVersionUrl('avatar', 'thumbnail')) ?></div>
        <div class="task-interaction-content">
            <?= $this->block('@content:begin') ?>

            <div class="task-interaction-meta d-flex justify-content-center mb-1">
                <div class="task-interaction-staff flex-grow-1 mr-2">
                    <?= Html::a(Html::encode($model->staff->account->username), ['/account/admin/staff/profile', 'id' => $model->staff_id]); ?>
                </div>
                <small class="task-interaction-time text-muted align-middle flex-shrink-0"
                       data-toggle="tooltip"
                       title="<?= Yii::$app->formatter->asDatetime($model->at) ?>">
                    <?= Yii::$app->formatter->asRelativeTime($model->at); ?>
                </small>
            </div>

            <div class="task-interaction-comment">
                <?= Yii::$app->formatter->asHtml($model->comment) ?>

                <?php if ($model->progress): ?>
                    <div class="d-flex w-100 mb-1 align-items-center task-interaction-progress-wrapper">
                        <div class="important mr-2 text-primary"><?= $model->progress * 100 ?>%</div>
                        <?= Progress::widget([
                            'percent' => $model->progress * 100,
                            'options' => [
                                'style' => 'height:4px;min-width: 75px',
                                'class' => 'flex-grow-1',
                            ],
                        ]);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($model->status_id): ?>
                    <div class="task-interaction-status flex-shrink-0 mb-1">
                        <?= Yii::t('app', 'Set status to: ') ?>
                        <span class="color-description mr-0 ml-1" style="background: <?= Html::encode($model->status->color_label); ?>"></span>
                        <span class="important" style="color:<?= Html::encode($model->status->color_label); ?>"><?= Html::encode($model->status->label) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($model->attachments)): ?>
                    <div class="attachments mt-2">
                        <?php foreach ($model->attachments AS $attachment): ?>
                            <?php
                            $metaData = $attachment->getFileMetaData('file');
                            $extension = explode('/', $metaData['type'], 2);
                            ?>
                            <a href="<?= $metaData['url'] ?>" target="_blank" data-lazy="0" class="attachment shadow-sm" data-toggle="tooltip" title="<?= Html::encode($metaData['name']) ?>">
                                <div class="attachment-preview">
                                    <?php
                                    if (ImageVersion::isImage($attachment->getFilePath('file'))) {
                                        echo Html::img($metaData['src']);
                                    } else {
                                        echo Html::tag('div', Html::tag('div', end($extension)), [
                                            'class' => 'attachment-extension',
                                        ]);
                                    }
                                    ?>
                                </div>
                                <div class="attachment-name"><?= $metaData['name'] ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>

            <?= $this->block('@content:end') ?>
        </div>
    </div>

<?= $this->block('@end') ?>