<?php

use modules\ui\widgets\Icon;

?>

<div class="side-panel-wrapper">
    <div class="side-panel-header">
        <h5 class="side-panel-title"><?= $this->title ?></h5>
        <a href="#" class="side-panel-close"><?= Icon::show('i8:multiply') ?></a>
    </div>

    <div class="side-panel-body">
        <?= $content ?>
    </div>
</div>
