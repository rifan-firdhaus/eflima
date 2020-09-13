<?php

use modules\account\web\admin\View;
use modules\support\models\KnowledgeBase;
use modules\support\widgets\inputs\KnowledgeBaseCategoryInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\inputs\TinyMceInput;
use yii\helpers\ArrayHelper;

/**
 * @var View          $this
 * @var KnowledgeBase $model
 * @var array         $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'knowledge-base-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

echo $form->fields([
    [
        'class' => CardField::class,
        'fields' => [
            [
                'attribute' => 'title',
            ],
            [
                'attribute' => 'category_id',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => KnowledgeBaseCategoryInput::class,
                    'aliasAttribute' => 'new_category',
                ],
            ],
            [
                'attribute' => 'content',
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => TinyMceInput::class,
                    'type' => TinyMceInput::TYPE_ADVANCED,
                    'inline' => true,
                ],
            ],
            [
                'class' => ActiveField::class,
                'attribute' => 'is_enabled',
                'label' => '',
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