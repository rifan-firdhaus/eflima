<?php

use modules\account\web\admin\View;
use modules\file_manager\helpers\ImageVersion;
use modules\support\models\TicketReply;
use yii\helpers\Html;

/**
 * @var View        $this
 * @var TicketReply $model
 */
?>
<div class="ticket-reply mb-3 <?= ($model->isStaff ? 'ticket-reply-staff' : 'ticket-reply-contact'); ?>  p-3 rounded">
    <div class="ticket-reply-header align-items-center mb-3 pb-3 d-flex">
        <div class="ticket-reply-avatar mr-3">
            <?= Html::img($model->account->getFileVersionUrl('avatar', 'thumbnail')) ?>
        </div>
        <div class="ticket-reply-meta">
            <div class="ticket-reply-name"><?= Html::encode($model->name) ?></div>
            <div class="ticket-reply-date">
                <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
                <small><?= Yii::$app->formatter->asRelativeTime($model->created_at) ?></small>
            </div>
        </div>
    </div>

    <div class="ticket-reply-content">
        <?= Yii::$app->formatter->asHtml($model->content) ?>

        <?php if ($model->attachments): ?>
            <div class="attachments mt-3">
                <?php foreach ($model->attachments AS $attachment): ?>
                    <?php
                    $metaData = $attachment->getFileMetaData('file');
                    $extension = explode('/', $metaData['type'], 2);
                    ?>
                    <a href="<?= $metaData['url'] ?>" target="_blank" data-lazy="0" class="attachment bg-really-light shadow-sm" data-toggle="tooltip" title="<?= Html::encode($metaData['name']) ?>">
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
</div>
