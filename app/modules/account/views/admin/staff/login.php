<?php

use modules\account\models\forms\staff_account\StaffAccountLogin;
use modules\account\web\admin\View;
use modules\ui\widgets\Card;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\Icon;
use yii\helpers\Html;

/**
 * @var View              $this
 * @var StaffAccountLogin $model
 */

$this->title = Yii::t('app', 'Login');
?>
<div class="container unauthenticated-container">
    <?php
    Card::begin([
        'title' => Yii::t('app', 'Login') . Html::tag('small', Yii::t('app', 'Enter your email and password to continue'), ['class' => 'd-block font-weight-light']),
        'encodeTitle' => false,
        'titleOptions' => [
            'class' => 'card-header-title text-center w-100',
        ],
        'headerOptions' => [
            'class' => 'card-header pb-0',
        ],
        'options' => [
            'class' => 'card unauthenticated-card rounded',
        ],
    ]);

    $form = Form::begin([
        'model' => $model,
        'enableTimestamp' => false,
        'lazy' => false,
        'actions' => [
            'secondary' => [
                'reset-password' => [
                    'action' => [
                        'label' => Yii::t('app', 'Forgot password?'),
                        'href' => '#',
                        'class' => 'btn btn-link pl-0',
                    ],
                ],
            ],
            'primary' => [
                'save' => [
                    'action' => Html::submitButton(Icon::show('i8:paper-plane') . Yii::t('app', 'Login'), ['class' => 'text-uppercase btn btn-primary btn-block']),
                ],
            ],
        ],
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

    Card::end();
    ?>
</div>
