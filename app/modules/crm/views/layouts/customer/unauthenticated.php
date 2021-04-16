<?php

use modules\core\web\View;
use modules\crm\assets\customer\UnauthenticatedAsset;
use yii\helpers\Html;

/**
 * @var string $content
 * @var View   $this
 */

UnauthenticatedAsset::register($this);
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
    <body>
        <?php $this->beginBody(); ?>
        <?= $content ?>
        <?php $this->endBody(); ?>
    </body>
</html>
<?php $this->endPage() ?>
