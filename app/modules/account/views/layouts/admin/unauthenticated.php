<?php

use modules\account\assets\admin\UnauthenticatedAsset;
use modules\account\web\admin\View;
use yii\helpers\Html;

/**
 * @var string $content
 * @var View   $this
 */

UnauthenticatedAsset::register($this);

$logoImage = Html::img('@web/public/img/logo-white.png', [
    'id' => 'logo',
    'alt' => Yii::t('app', 'Logo of Eflima'),
]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="eflima <?= implode(' ', $this->bodyClass) ?>">
        <?php $this->beginBody() ?>

        <div id="wrapper" class="d-flex w-100 h-100 flex-column justify-content-center align-items-center">

            <div id="header">
                <?= $this->block('@header:begin') ?>
                <div id="logo-wrapper">
                    <?= $this->block('@logo') ?>
                    <?= Html::a($logoImage, Yii::$app->getHomeUrl(), ['id' => 'logo-link']) ?>
                </div>
                <?= $this->block('@header:end') ?>
            </div>

            <div id="main">
                <?= $this->block('@main:begin'); ?>
                <?= $content ?>
                <?= $this->block('@main:end'); ?>
            </div>

            <div id="footer">
                <?= Yii::t('app', 'Eflima Engine v.{version}', [
                    'version' => Yii::$app->version,
                ]) ?>
            </div>
        </div>

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
