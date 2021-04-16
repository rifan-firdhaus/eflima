<?php

use modules\account\web\admin\View;
use modules\crm\widgets\inputs\LeadStatusInput;
use modules\finance\models\forms\proposal\ProposalBulkSetStatus;
use modules\finance\widgets\inputs\ProposalStatusInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var View                  $this
 * @var ProposalBulkSetStatus $model
 */


$this->title = Yii::t('app', 'Set Status');
$this->subTitle = Yii::t('app', '{number} Proposal', [
    'number' => count($model->ids),
]);
$this->icon = 'i8:handshake';

if (!isset($formOptions)) {
    $formOptions = [
        'action' => ['/finance/admin/proposal/bulk-set-status'],
    ];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'proposal-bulk-set-status-form',
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
                    'class' => ProposalStatusInput::class,
                ],
            ],
        ],
    ],
]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');
