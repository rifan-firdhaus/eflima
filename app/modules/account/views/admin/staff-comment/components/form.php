<?php

use modules\account\models\AccountComment;
use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\account\widgets\StaffCommentWidget;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\fields\MultiField;
use modules\ui\widgets\form\fields\RawField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\Icon;
use modules\ui\widgets\inputs\TinyMceInput;
use yii\helpers\Html;
use yii\web\JsExpression;

/**
 * @var View               $this
 * @var AccountComment     $model
 * @var array              $formOptions
 * @var StaffAccount       $account
 * @var StaffCommentWidget $widget
 */

$account = Yii::$app->user->identity;

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

if (isset($widget)) {
    $form = $widget->form = Form::begin($widget->form);

    $widget->form->model = $model;

} else {
    $form = Form::begin([
        'id' => 'account-comment-form',
        'model' => $model,
        'autoRenderActions' => false,
        'enableTimestamp' => false,
        'formActionsSections' => [
            'secondary' => [
                'sort' => -1,
                'class' => 'align-self-start d-flex ',
            ],
        ],
    ]);
}

$form->options['class'] = 'mce-d tox-d';

$form->formActionsSections['tinymce'] = [
    'sort' => -2,
    'class' => 'tinymce-toolbar',
];

echo $this->block('@form:begin', compact('form'));

$form->addAction(Html::submitButton(Icon::show('i8:paper-plane') . Yii::t('app', 'Send'), ['class' => 'btn btn-link']), 'save');
$form->addAction(FileUploaderInput::widget([
    'attribute' => 'uploaded_attachments[]',
    'model' => $model,
    'multiple' => true,
    'jsOptions' => [
        'templates' => [
            'input' => '<button type="button" data-fp-browse class="btn btn-link"><i class="icons8-attach mr-1 icons8-size"></i>' . Yii::t('app', 'Attach File') . '</button>',
            'items' => new JsExpression("$('#{$form->options['id']} .comment-attachment')"),
        ],
    ],
]), 'attach-file', 'secondary');
$form->addAction('', 'tinymce-toolbar', 'tinymce');

echo Html::activeHiddenInput($model, 'model');
echo Html::activeHiddenInput($model, 'model_id');

echo $form->fields([
    [
        'class' => ContainerField::class,
        'inputOnly' => true,
        'inputOptions' => ['class' => 'comment-item comment-item-me d-flex'],
        'fields' => [
            [
                'size' => '',
                'field' => [
                    'class' => RawField::class,
                    'inputOnly' => true,
                    'input' => Html::tag('div', Html::img($account->getFileVersionUrl('avatar', 'thumbnail')), ['class' => 'comment-avatar']),
                ],
            ],
            [
                'size' => 'w-100 comment-form-wrapper',
                'field' => [
                    'class' => MultiField::class,
                    'inputOnly' => true,
                    'fields' => [
                        [
                            'attribute' => 'comment',
                            'standalone' => true,
                            'label' => false,
                            'type' => ActiveField::TYPE_WIDGET,
                            'placeholder' => Yii::t('app', 'Write your comment here...'),
                            'inputOptions' => [
                                'class' => 'comment-content-input form-control',
                            ],
                            'widget' => [
                                'class' => TinyMceInput::class,
                                'inline' => true,
                                'type' => TinyMceInput::TYPE_BASIC,
                                'jsOptions' => [
                                    'fixed_toolbar_container' => "#{$form->options['id']} .tinymce-toolbar",
                                    'auto_focus' => false,
                                    'plugins' => 'autoresize table quickbars lists link image code imagetools paste searchreplace  codesample textpattern',
                                    'contextmenu' => "image media inserttable | paste pastetext searchreplace | pagebreak charmap | code",
                                    'menubar' => false,
                                    'statusbar' => false,
                                    'quickbars_insert_toolbar' => false,
                                    'toolbar_drawer' => 'floating',
                                    'quickbars_selection_toolbar' => 'bold underline italic strikethrough subscript superscript | forecolor | quicklink',
                                    'toolbar' => 'bold underline italic strikethrough subscript superscript | forecolor backcolor | bullist numlist outdent indent | image table link codesample',
                                ],
                            ],
                        ],
                        [
                            'class' => RawField::class,
                            'inputOnly' => true,
                            'input' => Html::tag('div', '', [
                                'class' => 'comment-attachment px-3',
                            ]),
                        ],
                        [
                            'class' => RawField::class,
                            'inputOnly' => true,
                            'input' => $form->renderActions(),
                        ],
                    ],
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');
