<?php

use modules\account\models\Staff;
use modules\account\web\admin\View;
use modules\ui\widgets\form\Form;
use yii\helpers\ArrayHelper;

/**
 * @var View          $this
 * @var Staff $model
 * @var array         $formOptions
 */

if (!isset($formOptions)) {
    $formOptions = [];
}

echo $this->block('@begin', [
    'formOptions' => &$formOptions,
]);

$form = Form::begin(ArrayHelper::merge([
    'id' => 'staff-account-form',
    'model' => $model,
], $formOptions));

echo $this->block('@form:begin', compact('form'));

$this->mainForm($form);

echo $form->fields([

]);

echo $this->block('@form:end', compact('form'));

Form::end();

echo $this->block('@end');