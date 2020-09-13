<?php

use modules\account\assets\admin\MainAsset;
use modules\account\web\admin\View;
use yii\helpers\Html;

/**
 * @var string $content
 * @var View   $this
 */

MainAsset::register($this);
$this->registerJsVar('messages', Yii::$app->session->getAllFlashes());
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
    <body class="eflima <?= implode(' ', $this->bodyClass) ?> sidebar-collapse">
        <?php $this->beginBody() ?>

        <?= $this->block('@begin'); ?>

        <div id="side-panel" class="side-panel"><?= $this->block('@side-panel'); ?></div>

        <div id="sidenav">
            <?= $this->render('components/sidenav') ?>
        </div>

        <div id="notification-panel" class="side-panel"></div>

        <div id="sidebar">
            <?= $this->render('components/sidebar') ?>
        </div>

        <div id="main">
            <?= $this->block('@main:begin'); ?>
            <?= $content ?>
            <?= $this->block('@main:end'); ?>
        </div>

        <div id="loading">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>

        <?= $this->block('@end'); ?>

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
