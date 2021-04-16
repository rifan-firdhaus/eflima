<?php

use modules\account\assets\admin\StaffDasshboardAsset;
use modules\account\web\admin\View;

/**
 * @var View $this
 */

$this->title = Yii::t('app', 'Dashboard');
$this->menu->active = 'main/dashboard';

StaffDasshboardAsset::register($this);

$this->registerJs("$(function(){
  $('.mas-con').packery({
    itemSelector: \".mas-item\",
    columnWidth: '.sizer',
    percentPosition: true
  });
  
  $('.mas-item').each(function(i,item){
      var draggabilly = new Draggabilly(item);

      $('.mas-con').packery(\"bindDraggabillyEvents\", draggabilly);
  });
})")
?>
<style>
.sizer {
}
.mas-item {
    box-shadow: 0px 0px 0px 1px red inset;
}
</style>
<div class="mas-con">
    <div class="sizer"></div>
    <div class="mas-item size-2" style="height: 100px">1</div>
    <div class="mas-item size-4" style="height: 200px">2</div>
    <div class="mas-item size-4" style="height: 100px">3</div>
    <div class="mas-item size-3" style="height: 50px">4</div>
    <div class="mas-item size-2" style="height: 100px">5</div>
    <div class="mas-item size-2" style="height: 150px">6</div>
</div>
