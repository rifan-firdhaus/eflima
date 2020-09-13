<?php

use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\ui\widgets\Menu;
use yii\helpers\Html;

/**
 * @var View         $this
 * @var StaffAccount $account
 */

$account = Yii::$app->user->identity;
$logoImage = Html::img('@web/public/img/logo-white.png', [
    'id' => 'logo',
    'alt' => Yii::t('app', 'Logo of Eflima'),
]);
?>
<?= $this->block('@begin') ?>

<div id="logo-wrapper" class="d-flex align-items-center justify-content-between">
    <?= $this->block('@logo') ?>
    <?= Html::a($logoImage, ['/'], ['id' => 'logo-link']) ?>
</div>

<div id="sidebar-header">
    <div class="container-fluid">

        <?= $this->block('@header:begin') ?>

        <div id="account-overview" class="d-flex">
            <div id="account-information" class="d-flex">
                <div class="account-avatar">
                    <?= Html::img($account->getFileVersionUrl('avatar', 'thumbnail', '@web/public/img/avatar.png')) ?>
                </div>
                <div class="account-detail">
                    <div class="account-identity text-truncate">
                        <span class="account-username"><?= Html::encode($account->username) ?></span> -
                        <span class="account-fullname"><?= Html::encode($account->profile->name) ?></span>
                    </div>
                    <small class="d-block account-group">as Staff</small>
                </div>
            </div>
        </div>

        <?= $this->block('@header:end') ?>

    </div>
</div>

<?= $this->block('@content') ?>

<?= Menu::widget([
    'items' => $this->menu->getTree('main'),
    'active' => substr($this->menu->active, 5),
    'itemOptions' => [
        'class' => 'nav-item',
    ],
    'linkOptions' => [
        'class' => 'nav-link',
    ],
    'subMenuOptions' => [
        'class' => 'nav flex-column',
    ],
    'options' => [
        'id' => 'sidebar-nav',
        'class' => 'nav sidebar-nav flex-column',
    ],
]);
?>

<?= $this->block('@end') ?>

