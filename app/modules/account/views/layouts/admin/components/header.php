<?php

use modules\account\web\admin\View;
use modules\ui\widgets\Icon;
use modules\ui\widgets\lazy\Lazy;
use yii\helpers\Html;

/**
 * @var View $this
 */

$breadcrumbs = $this->menu->breadcrumbs();

array_unshift($breadcrumbs, $this->menu->getItem('main/dashboard'));

$breadcrumbs = array_map(function ($item) {
    return [
        'label' => $item['label'],
        'url' => isset($item['url']) ? $item['url'] : '#',
        'icon' => isset($item['icon']) ? $item['icon'] : false,
    ];
}, $breadcrumbs);

if (!$this->icon && $this->menu->active) {
    $menu = $this->menu->getItem($this->menu->active);

    if (isset($menu['icon'])) {
        $this->icon = $menu['icon'];
    }
}

?>

<?= $this->block('@begin') ?>

<div class="toolbar container-fluid">
    <div class="d-flex main justify-content-between">

        <?= $this->block('@toolbar:begin') ?>

        <div class="left-toolbar d-flex align-self-center">
            <?= $this->block('@toolbar/left:begin') ?>

            <h1 class="toolbar-title w-100 align-self-center">
                <?php
                if ($this->icon) {
                    echo Icon::show($this->icon);
                }

                echo $this->title;

                if ($this->subTitle) {
                    echo Html::tag('small', Icon::show('i8:double-right') . Html::tag('span', $this->subTitle, ['class' => 'toolbar-subtitle']));
                }
                ?>
            </h1>

            <?php if (Lazy::isLazyModalRequest()) {
                echo Html::a(Icon::show('i8:multiply'), '#', ['class' => 'btn btn-link d-block d-sm-none btn-icon pr-0', 'data-modal-close' => 1]);
            }
            ?>

            <?= $this->block('@toolbar/left:end') ?>
        </div>

        <?= $this->block('@toolbar:middle') ?>

        <div class="align-self-center toolbar-action">
            <?php
            echo $this->block('@toolbar/action:begin');

            if (Lazy::isLazyModalRequest()) {
                $this->toolbar['close-modal'] = Html::a(Icon::show('i8:multiply'), '#', ['class' => 'btn btn-link d-none d-sm-inline-block btn-icon pr-0 ml-0', 'data-modal-close' => 1]);
            }

            echo $this->renderToolbar();

            echo $this->block('@toolbar/action:end');
            ?>
        </div>

        <?= $this->block('@toolbar:end') ?>
    </div>
</div>

<?= $this->block('@end') ?>
