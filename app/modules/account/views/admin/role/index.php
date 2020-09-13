<?php

use modules\account\assets\admin\RoleAsset;
use modules\account\web\admin\View;
use modules\ui\widgets\Card;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var array $tree
 * @var View  $this
 */

RoleAsset::register($this);

$this->title = Yii::t('app', 'Role & Permission');
$this->menu->active = 'main/admin/acl';
?>
    <div class="d-flex">
        <div class="w-50">
            <?php
            Card::begin([
                'bodyOptions' => false,
                'title' => Yii::t('app', 'Roles'),
            ]);
            ?>
            <table id="role-tree" class="table mb-0">
                <thead>
                    <tr>
                        <th><?= Yii::t('app', 'Role') ?></th>
                        <th class="text-center"><?= Yii::t('app', 'Total User') ?></th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td class="text-center"></td>
                        <td class="text-right"></td>
                    </tr>
                </tbody>
            </table>
            <?php Card::end(); ?>
        </div>
        <div class="w-50">
            <?php
            Card::begin([
                'bodyOptions' => false,
                'title' => Yii::t('app', 'Permissions'),
            ]);
            ?>
            <table id="permission-tree" class="table mb-0">
                <thead>
                    <tr>
                        <th><?= Yii::t('app', 'Role') ?></th>
                        <th class="text-center"><?= Yii::t('app', 'Total User') ?></th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td class="text-center"></td>
                        <td class="text-right"></td>
                    </tr>
                </tbody>
            </table>
            <?php Card::end(); ?>
        </div>
    </div>
<?php
$jsonTree = Json::encode($tree);
$options = Json::encode([
    'updateUrl' => Url::to(['/account/admin/role/update']),
    'moveUrl' => Url::to(['/account/admin/role/move']),
    'deleteUrl' => Url::to(['/account/admin/role/delete']),
]);
$this->registerJs("window.role = new Role($(\"#role-tree\"),{$jsonTree},{$options})");