<?php

use modules\ui\assets\Select2Asset;


//use modules\ui\widgets\inputs\MultipleEmailInput;
//
//echo MultipleEmailInput::widget([
//    'name' => 'dafaisdihf',
//    'source' => ['rifan','anggun']
//]);


Select2Asset::register($this)

?>

    <select style="width:100%" multiple id="aaa"></select>

<?php
$this->registerJs('$("#aaa").select2({tags:true,multiple:true,data: []})');
