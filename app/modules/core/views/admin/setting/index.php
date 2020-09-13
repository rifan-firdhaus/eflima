<?php

use modules\account\web\admin\View;
use modules\core\components\SettingRenderer;

/**
 * @var View            $this
 * @var SettingRenderer $renderer
 */


$this->menu->active = "setting/{$renderer->section}";
$activeMenu = $this->menu->getItem($this->menu->active);

if ($activeMenu) {
    $this->subTitle = $activeMenu['label'];
}

$this->beginContent('@modules/core/views/admin/setting/components/layout.php', compact('renderer'));

echo $this->block('@begin');

$renderer->render();

echo $this->block('@end');

$this->endContent();