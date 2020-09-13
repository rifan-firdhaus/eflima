<?php

use modules\account\web\admin\View;
use modules\crm\widgets\inputs\CustomerContactInput;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\support\models\Ticket;
use modules\support\widgets\inputs\TicketDepartmentInput;
use modules\support\widgets\inputs\TicketPriorityInput;
use modules\support\widgets\inputs\TicketStatusInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\TinyMceInput;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View   $this
 * @var Ticket $model
 * @var array  $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'ticket-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

if (Lazy::isLazyModalRequest()) {
    unset($this->toolbar['form-submit']);
}

echo $form->fields([
    [
        'class' => CardField::class,
        'inputOptions' => [
            'id' => Html::getInputId($model, 'basic_section'),
        ],
        'fields' => [
            [
                'attribute' => 'subject',
            ],
            [
                'attribute' => 'department_id',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => TicketDepartmentInput::class,
                ],
            ],
            [
                'attribute' => 'priority_id',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => TicketPriorityInput::class,
                ],
            ],
            [
                'attribute' => 'status_id',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => TicketStatusInput::class,
                ],
            ],
            [
                'attribute' => 'contact_id',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => CustomerContactInput::class,
                ],
            ],
        ],
    ],
    [
        'class' => CardField::class,
        'label' => Yii::t('app', 'Ticket Content'),
        'card' => [
            'icon' => 'i8:send-email',
        ],
        'inputOptions' => [
            'class' => 'pt-0 card-body',
            'id' => Html::getInputId($model, 'ticket_content'),
        ],
        'fields' => [
            [
                'attribute' => 'content',
                'type' => ActiveField::TYPE_WIDGET,
                'standalone' => true,
                'placeholder' => Yii::t('app', 'Write ticket content here...'),
                'inputOptions' => [
                    'style' => 'min-height: 7rem',
                    'class' => 'form-control',
                ],
                'widget' => [
                    'class' => TinyMceInput::class,
                    'inline' => true,
                ],
            ],
            [
                'attribute' => 'uploaded_attachments[]',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => FileUploaderInput::class,
                    'multiple' => true,
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');