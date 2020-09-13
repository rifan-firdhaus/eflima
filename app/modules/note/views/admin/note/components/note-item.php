<?php
/**
 * @var Note $model
 */

use modules\core\helpers\Common;
use modules\file_manager\helpers\ImageVersion;
use modules\note\models\Note;
use modules\ui\widgets\Icon;
use yii\helpers\Html;

?>
<div class="note-content">
    <?php if (!Common::isEmpty($model->title)): ?>
        <div class="note-item-title">
            <?= Html::encode($model->title) ?>
        </div>
    <?php endif; ?>
    <?= Yii::$app->formatter->asHtml($model->content) ?>
    <div class="note-item-attachment">
        <?php foreach ($model->attachments AS $attachment): ?>
            <?php
            $metaData = $attachment->getFileMetaData('file');
            $extension = explode('/', $metaData['type'], 2);
            ?>
            <a href="<?= $attachment->getFileUrl('file') ?>" class="file-uploader-item py-2 border-bottom d-flex align-items-center">
                <div class="file-uploader-thumbnail mr-2">
                    <?php
                    if (ImageVersion::isImage($attachment->getFilePath('file'))) {
                        echo Html::img($metaData['src'], [
                            'class' => 'picture-uploader-thumbnail-image',
                        ]);
                    } else {
                        echo Html::tag('div', Html::tag('div', end($extension)), [
                            'class' => 'picture-uploader-thumbnail-custom',
                        ]);
                    }
                    ?>
                </div>
                <div class="file-uploader-metadata flex-grow-1">
                    <div class="file-uploader-name"><?= Html::encode($metaData['name']) ?></div>
                    <div class="file-uploader-size"><?= Yii::$app->formatter->asShortSize($metaData['size'], 0) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="note-item-relation mt-2 font-size-sm text-muted">

        <?php
        if (!empty($model->model)) {
            $object = $model->getRelatedObject();

            $relatedRecordName = $model->relatedObject->getLink($model->relatedModel);

            if (is_null($relatedRecordName)) {
                $relatedRecordName = $model->relatedObject->getName($model->relatedModel);
            }

            $relationLabel = Yii::t('app', 'Related to {model}: {model_name}', [
                'model' => $model->relatedObject->getLabel(),
                'model_name' => $relatedRecordName,
            ]);

            echo Html::tag('div', Icon::show('i8:link', ['class' => 'icon mr-1 icons8-size']) . $relationLabel, ['class' => 'text-truncate']);
        }

        if ($model->is_private) {
            $visibilityIcon = Icon::show('i8:lock', [
                'class' => 'icon icons8-size mr-1',
                'title' => Yii::t('app', 'Private Note'),
                'data-toggle' => 'tooltip',
            ]);

            echo Html::tag('div', $visibilityIcon . Yii::t('app', 'Private Note'));
        } else {
            $visibilityIcon = Icon::show('i8:globe', [
                'class' => 'icon icons8-size mr-1',
                'title' => Yii::t('app', 'Public Note'),
                'data-toggle' => 'tooltip',
            ]);

            echo Html::tag('div', $visibilityIcon . Yii::t('app', 'Public Note'));
        }

        echo Html::tag('div', Icon::show('i8:time', ['class' => 'icon icons8-size mr-1']) . Yii::$app->formatter->asDatetime($model->created_at));

        ?>
    </div>

    <div class="form-action">
        <div>
            <?= Html::a(Icon::show('i8:edit'), ['/note/admin/note/update', 'id' => $model->id], [
                'class' => 'btn-update-note btn-icon btn btn-link btn-lg',
            ]) ?>
            <?= Html::button(Icon::show('i8:push-pin'), [
                'class' => 'btn-pin-note btn-icon btn btn-link btn-lg',
            ]) ?>
        </div>
        <div class="ml-auto">
            <?= Html::a(Icon::show('i8:trash'), ['/note/admin/note/delete', 'id' => $model->id], [
                'class' => 'btn-remove-note btn-icon text-danger btn btn-link btn-lg',
            ]) ?>
        </div>
    </div>
</div>
