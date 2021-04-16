<?php

use modules\account\web\admin\View;
use modules\quick_access\components\QuickSearch;
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
            <div class="dropdown dropleft dropdown-keep-open quick-search-model">
                <a href="#" data-lazy="0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?= Icon::show('i8:advanced-search') ?>
                </a>
                <div class="dropdown-menu pt-3 px-3 pb-0" aria-labelledby="dropdownMenuButton">
                    <div class="d-flex flex-column">
                        <?php foreach (QuickSearch::map() AS $id => $label): ?>
                            <?= Html::checkbox('model[]', true, [
                                'custom' => true,
                                'uncheck' => false,
                                'value' => $id,
                                'label' => $label,
                                'containerOptions' => [
                                    'class' => 'mr-3 mb-3',
                                ],
                            ]) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?= Html::endForm(); ?>

    <div class="quick-search-result">

    </div>
</div>
