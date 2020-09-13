<?php

use modules\account\web\admin\View;
use modules\crm\assets\admin\CustomerFormAsset;
use modules\crm\models\Customer;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\note\assets\admin\NoteForm;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\RawField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\Icon;
use modules\ui\widgets\inputs\TinyMceInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * @var View     $this
 * @var Customer $model
 * @var array    $formOptions
 */

NoteForm::register($this);

$rand = rand(1, 9000000);
CustomerFormAsset::register($this);
$attachments = [];

foreach ($model->attachments AS $attachment) {
    $attachments[] = $attachment->getFileMetaData('file');
}

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'model' => $model,
    'layout' => Form::LAYOUT_VERTICAL,
    'id' => 'note-form',
    'enableClient' => false,
    'options' => [
        'class' => 'note-form',
    ],
    'lazy' => [
        'jsOptions' => [
            'pushState' => false,
        ],
    ],
    'autoRenderActions' => false,
    'enableTimestamp' => false,
    'formActionsSections' => [
        'secondary' => [
            'sort' => -1,
            'class' => 'align-self-start d-flex ',
        ],
    ],
], $formOptions));

echo Html::activeHiddenInput($model, 'model');
echo Html::activeHiddenInput($model, 'model_id');

$form->addAction(Html::submitButton(Icon::show('i8:paper-plane') . Yii::t('app', 'Save'), ['class' => 'btn btn-lg btn-link']), 'save');
$form->addAction(Html::activeHiddenInput($model, 'is_private', ['class' => 'note-is-private-input']), 'is-private', 'secondary');
$form->addAction(FileUploaderInput::widget([
    'attribute' => 'uploaded_attachments[]',
    'model' => $model,
    'multiple' => true,
    'options' => [
        'class' => 'note-attachment-input',
    ],
    'jsOptions' => [
        'thumbnailSize' => 30,
        'values' => $attachments,
        'templates' => [
            'input' => '<button type="button" data-fp-browse class="btn btn-link btn-lg btn-icon"><i class="icons8-attach icons8-size"></i></button>',
            'items' => new JsExpression("$('#note-form-{$this->uniqueId} .note-attachment')"),
            'item' => '<div class="file-uploader-item py-2 border-bottom d-flex align-items-center">
                        <div class="file-uploader-thumbnail mr-2" data-fp-file-thumbnail></div>  
                        <div class="file-uploader-metadata flex-grow-1">
                           <div class="file-uploader-name" data-fp-file-name></div>
                           <div class="file-uploader-size" data-fp-file-size></div>
                        </div> 
                        <div class="file-uploader-action">
                            <a href="javascript:void(0)" data-lazy="0" data-fp-file-view><i class="icons8-eye icons8 icons8-size"></i></a>
                            <a href="javascript:void(0)" data-lazy="0" class="text-danger" data-fp-file-remove><i class="icons8-trash icons8 icons8-size"></i></a>
                        </div>
                      </div>',
        ],
    ],
]), 'attach-file', 'secondary');
echo $this->block('@form:begin', compact('form'));

echo $form->fields([
    [
        'attribute' => 'title',
        'inputOnly' => true,
        'type' => ActiveField::TYPE_TEXTAREA,
        'placeholder' => Yii::t('app', 'Title'),
        'inputOptions' => [
            'rows' => 1,
            'class' => 'note-title-input',
        ],
    ],
    [
        'attribute' => 'content',
        'type' => ActiveField::TYPE_WIDGET,
        'inputOnly' => true,
        'inputOptions' => [
            'class' => ['note-content-input','widget' => ''],
        ],
        'placeholder' => Yii::t('app', 'Write your note here...'),
        'widget' => [
            'class' => TinyMceInput::class,
            'type' => TinyMceInput::TYPE_FLOATING,
            'inline' => true,
        ],
    ],

    [
        'class' => RawField::class,
        'inputOnly' => true,
        'input' => Html::tag('div', '', [
            'class' => 'note-attachment',
        ]),
    ],
    [
        'class' => RawField::class,
        'inputOnly' => true,
        'input' => $form->renderActions(),
    ],
]);

echo $this->block('@form:end', compact('form'));

$jsOptions = Json::encode([]);

$this->registerJs("$('#{$form->id}').noteForm({$jsOptions})");

Form::end();

echo $this->block('@end');
