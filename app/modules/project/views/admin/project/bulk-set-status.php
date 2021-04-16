<?php

use modules\account\web\admin\View;
use modules\finance\widgets\inputs\ProposalStatusInput;
use modules\project\models\forms\project\ProjectBulkSetStatus;
use modules\project\widgets\inputs\ProjectStatusInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View                 $this
 * @var ProjectBulkSetStatus $model
 */


$this->title = Yii::t('app', 'Set Status');
$this->subTitle = Yii::t('app', '{number} Project', [
    'number' => count($model->ids),
]);
$this->icon = 'i8:idea';

if (!isset($formOptions)) {
    $formOptions = [
        'action' => ['/project/admin/project/bulk-set-status'],
    ];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'project-bulk-set-status-form',
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
                'attribute' => 'status_id',
                'type' => ActiveField::TYPE_WIDGET,
                'inputOnly' => true,
                'widget' => [
                    'class' => ProjectStatusInput::class,
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');
