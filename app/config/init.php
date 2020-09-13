<?php
Yii::setAlias('app', dirname(__DIR__));
Yii::setAlias('modules', '@app/modules');
Yii::setAlias('vendor', '@app/vendor');

Yii::$classMap['yii\base\ArrayableTrait'] = Yii::getAlias('@modules/core/base/ArrayableTrait.php');
Yii::$classMap['yii\helpers\ArrayHelper'] = Yii::getAlias('@modules/core/helpers/ArrayHelper.php');
Yii::$classMap['yii\helpers\Html'] = Yii::getAlias('@modules/ui/helpers/Html.php');
Yii::$classMap['yii\base\Widget'] = Yii::getAlias('@modules/core/base/Widget.php');
