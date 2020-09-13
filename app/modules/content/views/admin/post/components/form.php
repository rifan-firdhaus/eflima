<?php

use modules\account\web\admin\View;
use modules\content\models\Post;
use modules\content\widgets\inputs\PostInput;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\TinyMceInput;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;

/**
 * @var View  $this
 * @var Post  $model
 * @var array $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'post-form',
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
            ],
            [
                'attribute' => 'uploaded_picture',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => FileUploaderInput::class,
                ],
            ],
            [
                'attribute' => 'content',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => TinyMceInput::class,
                    'inline' => true,
                ],
            ],
            [
                'attribute' => 'is_published',
                'type' => ActiveField::TYPE_CHECKBOX,
                'label' => '',
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