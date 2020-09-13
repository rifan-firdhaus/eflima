<?php

use modules\account\web\admin\View;

/**
 * @var View $this
 */

$this->title = Yii::t('app', 'Settings');

$this->beginContent('@modules/account/views/layouts/admin/components/side-panel-layout.php');

echo $this->render('components/menu');

$this->endContent();