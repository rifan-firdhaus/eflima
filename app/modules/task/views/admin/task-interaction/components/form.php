<?php

use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\task\assets\admin\TaskInteractionFormAsset;
use modules\task\models\Task;
use modules\task\models\TaskInteraction;
use modules\task\widgets\inputs\TaskStatusInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\fields\MultiField;
use modules\ui\widgets\form\fields\RawField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\Icon;
use modules\ui\widgets\inputs\RangeInput;
use modules\ui\widgets\inputs\TinyMceInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;

TaskInteractionFormAsset::register($this);

/**
 * @var View            $this
 * @var TaskInteraction $model
 * @var array           $formOptions
 * @var StaffAccount    $account
 */

$account = Yii::$app->user->identity;

if (!isset($formOptions)) {
    $formOptions = [];
}

if (!isset($formOptions['action'])) {
    $formOptions['action'] = $model->isNewRecord ? ['/task/admin/task-interaction/add', 'task_id' => $model->task_id] : ['/task/admin/task-interaction/update', 'id' => $model->id];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'task-interaction-form',
    'autoRenderActions' => false,
    'enableTimestamp' => false,
    'model' => $model,
    'formActionsSections' => [
        'secondary' => [
            'sort' => -1,
            'class' => 'align-self-center align-items-center d-flex ',
        ],
    ],
], $formOptions));

$form->addAction(Html::submitButton(Icon::show('i8:paper-plane') . Yii::t('app', 'Send'), ['class' => 'btn btn-link']), 'save');
$form->addAction(FileUploaderInput::widget([
    'attribute' => 'uploaded_attachments[]',
    'model' => $model,
    'multiple' => true,
    'jsOptions' => [
        'templates' => [
            'input' => '<button type="button" data-fp-browse class="btn btn-link"><i class="icons8-attach mr-1 icons8-size"></i>' . Yii::t('app', 'Attach File') . '</button>',
            'items' => new JsExpression('$(".task-interaction-attachment")'),
        ],
    ],
]), 'attach-file', 'secondary');
$form->addAction(Html::tag('div', TaskStatusInput::widget([
    'model' => $model,
    'attribute' => 'status_id',
])), 'task-interaction-status-wrapper', 'secondary');


if ($model->task->progress_calculation === Task::PROGRESS_CALCULATION_OWN) {
    $progressInput = Html::tag('div', RangeInput::widget([
        'model' => $model,
        'attribute' => 'progress',
        'max' => 100,
        'options' => [
            'value' => $model->progress ? $model->progress * 100 : 0,
        ],
        'jsOptions' => [
            'prefix' => Yii::t('app', 'Set progress to:'),
            'postfix' => '%',
            'hide_min_max' => true,
            'hide_from_to' => false,
            'force_edges' => true,
            'grid_margin' => false,
        ],
    ]), [
        'class' => 'task-interaction-progress-wrapper',
    ]);

    $form->addAction($progressInput, 'task-interaction-progress-wrapper', 'secondary');
}

echo $this->block('@form:begin', compact('form'));

echo $form->fields([
    [
        'class' => ContainerField::class,
        'inputOnly' => true,
        'inputOptions' => ['class' => 'task-interaction-item d-flex'],
        'fields' => [
            [
                'size' => '',
                'field' => [
                    'class' => RawField::class,
                    'inputOnly' => true,
                    'input' => Html::tag('div', Html::img($account->getFileVersionUrl('avatar', 'thumbnail')), ['class' => 'task-interaction-avatar']),
                ],
            ],
            [
                'size' => 'w-100 form-wrapper',
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
                            'widget' => [
                                'class' => TinyMceInput::class,
                                'inline' => true,
                                'type' => TinyMceInput::TYPE_BASIC,
                            ],
                        ],
                        [
                            'class' => RawField::class,
                            'inputOnly' => true,
                            'input' => Html::tag('div', '', [
                                'class' => 'task-interaction-attachment px-3',
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