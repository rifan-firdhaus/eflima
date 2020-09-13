<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Icon;
use yii\helpers\Html;

/**
 * @var View $this
 */
?>


<div class="quick-search-container" style="display: none">
    <div class="quick-search-close" data-quick-search-close><?= Icon::show('i8:multiply') ?></div>
    <div class="quick-search-overlay"></div>
    <?= Html::beginForm(['/quick_access/admin/default/quick-search'], 'get', ['class' => 'quick-search-form']); ?>
    <div class="quick-search-header">
        <div class="quick-search-input-wrapper">
            <?= Icon::show('i8:search'); ?>
            <input class="quick-search-input form-control" type="text" placeholder="<?= Yii::t('app', 'Search...') ?>" />
        </div>
    </div>
    <?= Html::endForm(); ?>

    <div class="quick-search-result">

    </div>
</div>