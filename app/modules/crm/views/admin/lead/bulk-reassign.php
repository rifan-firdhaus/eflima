<?php

use modules\account\web\admin\View;
use modules\account\widgets\inputs\StaffInput;
use modules\crm\models\forms\lead\LeadBulkReassign;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View             $this
 * @var LeadBulkReassign $model
 */


$this->title = Yii::t('app', 'Reassign');
$this->subTitle = Yii::t('app', '{number} Lead', [
    'number' => count($model->ids),
]);
$this->icon = 'i8:connect';

if (!isset($formOptions)) {
    $formOptions = [
        'action' => ['/crm/admin/lead/bulk-reassign'],
    ];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'lead-bulk-reassign-form',
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
                'attribute' => 'assignee_ids',
                'type' => ActiveField::TYPE_WIDGET,
                'inputOnly' => true,
                'widget' => [
                    'class' => StaffInput::class,
                    'multiple' => true,
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');
