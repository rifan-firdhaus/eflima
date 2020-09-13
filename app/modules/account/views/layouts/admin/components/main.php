<?php

use modules\account\web\admin\View;

/**
 * @var View   $this
 * @var string $content
 */

?>
<div class="header">
    <?= $this->render('header') ?>
</div>

<div class="content <?= ($this->fullHeightContent ? 'h-100' : '') ?>">
    <div class="container-fluid <?= ($this->fullHeightContent ? 'h-100' : '') ?>">
        <?= $content ?>
    </div>
</div>