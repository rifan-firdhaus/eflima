<?php

use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\support\assets\admin\TicketReplyFormAsset;
use modules\support\models\TicketReply;
use modules\support\widgets\inputs\TicketPredefinedReplyInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\fields\RawField;
use modules\ui\widgets\form\fields\RegularField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\Icon;
use modules\ui\widgets\inputs\MultipleEmailInput;
use modules\ui\widgets\inputs\TinyMceInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var View         $this
 * @var TicketReply  $model
 * @var array        $formOptions
 * @var StaffAccount $account
 */

TicketReplyFormAsset::register($this);

$account = Yii::$app->user->identity;

if (!isset($formOptions)) {
    $formOptions = [];
}

if (!isset($formOptions['action'])) {
    $formOptions['action'] = ['/support/admin/ticket-reply/add', 'ticket_id' => $model->ticket_id];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$horizontalOptions = [
    'field' => 'd-flex ticket-reply-field',
    'label' => 'ticket-reply-label align-self-center',
    'input' => 'ticket-reply-input w-100',
];

$ccAndBcchorizontalOptions = [
    'field' => 'ticket-reply-field',
    'label' => 'ticket-reply-label align-self-center',
    'input' => 'ticket-reply-input w-100',
];

$form = Form::begin(ArrayHelper::merge([
    'id' => 'ticket-reply-form',
    'autoRenderActions' => false,
    'enableTimestamp' => false,
    'layout' => Form::LAYOUT_HORIZONTAL,
    'model' => $model,
    'formActionsOptions' => [
        'class' => 'form-action flex-wrap',
    ],
    'formActionsSections' => [
        'tinymce-toolbar' => [
            'sort' => -3,
            'class' => 'flex-grow-1 flex-shrink-0 w-100',
            'id' => 'ticket-reply-tinymce-toolbar-container',
        ],
        'secondary' => [
            'sort' => -1,
            'class' => 'align-self-start d-flex ',
        ],
    ],
], $formOptions));

$fileUploader = FileUploaderInput::widget([
    'attribute' => 'uploaded_attachments[]',
    'model' => $model,
    'multiple' => true,
    'jsOptions' => [
        'templates' => [
            'input' => '<button type="button" data-fp-browse class="btn btn-link"><i class="icons8-attach mr-1 icons8-size"></i>' . Yii::t('app', 'Attach File') . '</button>',
            'item' => '<div class="file-uploader-item p-2 w-50 border-bottom d-flex align-items-center">
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
            'items' => new JsExpression('$(".ticket-reply-attachment")'),
        ],
    ],
]);

$predefinedReplyInput = TicketPredefinedReplyInput::widget([
    'prompt' => '',
    'name' => 'predefined-value',
    'id' => 'ticket-reply-predefined-reply',
]);
$predefinedReplyTrigger = Html::a(Icon::show('i8:index', ['class' => 'icon']) . Yii::t('app', 'Predefined Reply'), '#', ['class' => 'btn btn-link']);

$form->addAction($fileUploader, 'attach-file', 'secondary');
$form->addAction(Html::submitButton(Icon::show('i8:paper-plane', ['class' => 'icon']) . Yii::t('app', 'Send'), ['class' => 'btn text-uppercase btn-link']), 'save');
$form->addAction(Html::tag('div', $predefinedReplyInput . $predefinedReplyTrigger, ['class' => 'ticket-reply-predefined-reply']), 'predefined-reply', 'secondary');
$form->addAction(Html::a(Icon::show('i8:help', ['class' => 'icon']) . Yii::t('app', 'Knowledge Base'), '#', ['class' => 'btn btn-link']), 'knowledge-base', 'secondary');
$form->addAction(Html::tag('div', '', ['id' => 'ticket-reply-tinymce-toolbar']), 'tinymce-toolbar', 'tinymce-toolbar');


echo $this->block('@form:begin', compact('form'));

echo $form->fields([
    [
        'class' => CardField::class,
        'label' => Yii::t('app', 'Reply Ticket'),
        'inputOptions' => [
            'id' => Html::getInputId($model, 'general_section'),
            'data-rid' => Html::getRealInputId($model, 'general_section'),
        ],
        'card' => [
            'icon' => 'i8:reply',
            'bodyOptions' => [
                'class' => 'border-top',
            ],
        ],
        'fields' => [
            [
                'class' => RegularField::class,
                'name' => 'from',
                'label' => Yii::t('app', 'From'),
                'value' => "{$account->profile->name} <{$account->email}>",
                'horizontalCssClasses' => $horizontalOptions,
                'inputOptions' => [
                    'readonly' => true,
                    'class' => 'form-control',
                ],
            ],
            [
                'class' => RegularField::class,
                'name' => 'to',
                'label' => Yii::t('app', 'To'),
                'value' => "{$model->ticket->contact->name} <{$model->ticket->contact->email}>",
                'horizontalCssClasses' => $horizontalOptions,
                'inputGroups' => [
                    [
                        'content' => Html::a(Yii::t('app', 'CC'), '#', ['class' => 'btn btn-link btn-cc border-left']),
                        'asText' => false,
                        'position' => 'append',
                    ],
                    [
                        'content' => Html::a(Yii::t('app', 'BCC'), '#', ['class' => 'btn btn-link btn-bcc border-left']),
                        'asText' => false,
                        'position' => 'append',
                    ],
                ],
                'inputOptions' => [
                    'readonly' => true,
                    'class' => 'form-control',
                ],
            ],
            [
                'attribute' => 'carbon_copy',
                'label' => Yii::t('app', 'CC'),
                'horizontalCssClasses' => $ccAndBcchorizontalOptions,
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => MultipleEmailInput::class,
                ],
            ],
            [
                'attribute' => 'blind_carbon_copy',
                'label' => Yii::t('app', 'BCC'),
                'horizontalCssClasses' => $ccAndBcchorizontalOptions,
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => MultipleEmailInput::class,
                ],
            ],
            [
                'attribute' => 'content',
                'standalone' => true,
                'type' => ActiveField::TYPE_WIDGET,
                'placeholder' => Yii::t('app', 'Write your reply here...'),
                'options' => [
                    'class' => 'ticket-reply-field ticket-reply-content-field',
                ],
                'widget' => [
                    'class' => TinyMceInput::class,
                    'type' => TinyMceInput::TYPE_BASIC,
                    'inline' => true,
                    'jsOptions' => [
                        'fixed_toolbar_container' => '#ticket-reply-tinymce-toolbar',
                    ],
                ],
            ],
            [
                'class' => RawField::class,
                'inputOnly' => true,
                'input' => Html::tag('div', '', [
                    'class' => 'ticket-reply-attachment flex-wrap d-flex',
                ]),
            ],
            [
                'class' => RawField::class,
                'inputOnly' => true,
                'input' => $form->renderActions(),
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

$jsOptions = Json::encode([
    'predefinedReplyUrl' => Url::to(['/support/admin/ticket-predefined-reply/get']),
]);

$this->registerJs("$('#{$form->id}').ticketReplyForm({$jsOptions})");

Form::end();

echo $this->block('@end');
