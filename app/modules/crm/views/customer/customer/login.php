<?php
/**
 * @var View                        $this
 * @var CustomerContactAccountLogin $model
 */

use modules\core\web\View;
use modules\crm\models\forms\customer_contact_account\CustomerContactAccountLogin;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\Icon;

$form = Form::begin([
    'model' => $model,
    'enableTimestamp' => false,
    'lazy' => false,
]);

echo $form->fields([
    [
        'attribute' => 'username',
        'standalone' => true,
        'placeholder' => true,
        'inputGroups' => [
            'before' => Icon::show('i8:account', ['class' => 'icon icons8-size text-primary']),
        ],
    ],
    [
        'attribute' => 'password',
        'standalone' => true,
        'placeholder' => true,
        'type' => ActiveField::TYPE_PASSWORD,
        'hint' => false,
        'inputGroups' => [
            'before' => Icon::show('i8:lock', ['class' => 'icon icons8-size text-primary']),
        ],
    ],
    [
        'standalone' => true,
        'attribute' => 'remember_me',
        'type' => ActiveField::TYPE_CHECKBOX,
        'inputOptions' => [
            'custom' => true,
        ],
    ],
]);


Form::end();
?>


