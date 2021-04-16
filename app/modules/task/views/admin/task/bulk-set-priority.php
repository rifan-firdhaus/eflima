<?php

use modules\account\web\admin\View;
use modules\crm\widgets\inputs\CustomerGroupInput;
use modules\task\models\forms\task\TaskBulkSetPriority;
use modules\task\models\forms\task\TaskBulkSetStatus;
use modules\task\widgets\inputs\TaskPriorityInput;
use modules\task\widgets\inputs\TaskStatusInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View              $this
 * @var TaskBulkSetPriority $model
 */


$this->title = Yii::t('app', 'Set Priority');
$this->subTitle = Yii::t('app', '{number} Task',[
    'number' => count($model->ids)
]);
$this->icon = 'i8:checked';

if (!isset($formOptions)) {
    $formOptions = [
        'action' => ['/task/admin/task/bulk-set-priority'],
    ];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'task-bulk-set-priority-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

if (Lazy::isLazyModalRequest()) {
    unset($this->toolbar['form-submit']);
}

foreach ($model->ids AS $key => $id) {
    echo Html::activeHiddenInput($model, 'ids[]', [
        'value' => $id,
        'id' => Html::getInputId($model, 'ids[]') . '-' . $key,
    ]);
}

echo $form->fields([
    [
        'class' => CardField::class,
        'fields' => [
            [
                'attribute' => 'priority_id',
                'type' => ActiveField::TYPE_WIDGET,
                'inputOnly' => true,
                'widget' => [
                    'class' => TaskPriorityInput::class,
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');
