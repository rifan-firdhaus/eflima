<?php

use modules\account\web\admin\View;
use modules\core\components\SettingRenderer;
use modules\ui\widgets\Menu;


/**
 * @var View            $this
 * @var SettingRenderer $renderer
 * @var string          $content
 */

$this->title = Yii::t('app', 'Settings');

echo $this->block('@begin');

$this->fullHeightContent = true;
?>
    <div class="d-flex h-100">
        <div class="border-right d-none d-sm-block flex-shrink-0 flex-grow-0 h-100 bg-really-light overflow-auto">
            <?= Menu::widget([
                'items' => $this->menu->getTree('setting'),
                'active' => substr($this->menu->active, 8),
                'itemOptions' => [
                    'class' => 'nav-item',
                ],
                'linkOptions' => [
                    'class' => 'nav-link',
                ],
                'subMenuOptions' => [
                    'class' => 'nav',
                ],
                'options' => [
                    'class' => 'sidebar-nav position-sticky nav flex-column menu',
                ],
            ]); ?>
        </div>
        <div class="flex-grow-1 h-100 overflow-auto">
            <?= $content ?>
        </div>
    </div>
<?php
echo $this->block('@end');
