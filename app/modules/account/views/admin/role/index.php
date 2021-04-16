<?php

use modules\account\assets\admin\RoleAsset;
use modules\account\web\admin\View;
use modules\ui\widgets\Card;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var array $tree
 * @var View  $this
 */

RoleAsset::register($this);

$this->title = Yii::t('app', 'Role & Permission');
$this->menu->active = 'main/admin/acl';
$this->fullHeightContent = true;
?>
    <div class="d-flex h-100">
        <div class="w-50 h-100 overflow-auto border-right">
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
                        <th class="text-center"></th>
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
        <div class="w-50 h-100 border-left overflow-auto">
            <?php
            Card::begin([
                'bodyOptions' => false,
                'title' => Yii::t('app', 'Permissions'),
            ]);
            ?>
            <table id="permission-tree" class="table mb-0">
                <thead>
                    <tr>
                        <th><?= Yii::t('app', 'Permision') ?></th>
                        <th class="text-right"><?= Yii::t('app', 'Access') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td class="text-right"></td>
                    </tr>
                </tbody>
            </table>
            <?php Card::end(); ?>
        </div>
    </div>
<?php
$roleData = Json::encode(ArrayHelper::toArray($tree, [], true, ['totalAccount']));
$roleOptions = Json::encode([
    'updateUrl' => Url::to(['/account/admin/role/update']),
    'permissionUrl' => Url::to(['/account/admin/permission/index']),
    'moveUrl' => Url::to(['/account/admin/role/move']),
    'deleteUrl' => Url::to(['/account/admin/role/delete']),
]);
$this->registerJs("window.role = new Role($(\"#role-tree\"),{$roleData},{$roleOptions})");

$permissionOptions = Json::encode([
    'accessUrl' => Url::to(['/account/admin/permission/set-access']),
]);
$this->registerJs("window.permission = new Permission($(\"#permission-tree\"),{$permissionOptions})");
