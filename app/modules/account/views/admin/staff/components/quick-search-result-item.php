<?php

use modules\account\models\Staff;
use modules\account\web\admin\View;
use yii\helpers\Html;

/**
 * @var View  $this
 * @var Staff $model
 */

?>
<div class="quick-search-result-item">
    <div class="header">
        <div class="title d-flex align-items-center">
            <div class="avatar mr-3 rounded-circle" style="width: 50px;height:50px;overflow: hidden">
                <?= Html::img($model->account->getFileVersionUrl('avatar', 'thumbnail'), ['class' => 'w-100']) ?>
            </div>
            <div class="meta">
                <div class="font-size-lg"><?= Html::encode($model->account->username) ?></div>
                <small><?= Html::encode($model->name) ?></small>
            </div>
        </div>
    </div>
</div>
