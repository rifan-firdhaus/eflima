<?php

use modules\account\web\admin\View;
use modules\support\models\TicketPredefinedReply;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\TinyMceInput;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;

/**
 * @var View                  $this
 * @var TicketPredefinedReply $model
 * @var array                 $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'ticket-predefined-reply-form',
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
        'fields' => [
            [
                'attribute' => 'title',
                'standalone' => true,
                'placeholder' => true,
            ],
            [
                'attribute' => 'content',
                'standalone' => true,
                'placeholder' => Yii::t('app', 'Write the content of your reply here...'),
                'class' => ActiveField::class,
                'type' => ActiveField::TYPE_WIDGET,
                'inputOptions' => [
                    'class' => 'form-control',
                    'style' => ['min-height' => '8rem'],
                ],
                'widget' => [
                    'class' => TinyMceInput::class,
                    'inline' => true,
                ],
            ],
            [
                'class' => ActiveField::class,
                'attribute' => 'is_enabled',
                'standalone' => true,
                'placeholder' => true,
                'type' => ActiveField::TYPE_CHECKBOX,
                'inputOptions' => [
                    'custom' => true,
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');