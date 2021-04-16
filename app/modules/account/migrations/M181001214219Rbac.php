<?php namespace modules\account\migrations;

use modules\account\models\Staff;
use modules\account\rbac\DbManager;
use Yii;
use yii\db\Migration;

/**
 * Class M190413073345Rbac
 */
class M181001214219Rbac extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        $time = time();
        $this->beginCommand('Register permissions');

        if (!$auth->installPermissions($this->permissions())) {
            return false;
        }

        $this->endCommand($time);

        $time = time();
        $this->beginCommand('Register roles');

        if (!$this->installRole()) {
            return false;
        }

        $this->endCommand($time);
    }

    /**
     * @return array
     */
    public function permissions()
    {
        return [
            'admin.root' => [
                'description' => 'Give full access to application',
            ],
            'admin.staff' => [
                'parent' => 'admin.root',
                'description' => 'Manage Staffs',
            ],
            'admin.staff.list' => [
                'parent' => 'admin.staff',
                'description' => 'List of Staff',
            ],
            'admin.staff.add' => [
                'parent' => 'admin.staff',
                'description' => 'Add Staff',
            ],
            'admin.staff.update' => [
                'parent' => 'admin.staff',
                'description' => 'Update Staff',
            ],
            'admin.staff.view' => [
                'parent' => 'admin.staff',
                'description' => 'View Staff\'s Profile',
            ],
            'admin.staff.update.password' => [
                'parent' => 'admin.staff.update',
                'description' => 'Update Staf\'s Password',
            ],
            'admin.staff.delete' => [
                'parent' => 'admin.staff',
                'description' => 'Delete Staff',
            ],
            'admin.staff.block' => [
                'parent' => 'admin.staff',
                'description' => 'Block Staff',
            ],
            'admin.staff.unblock' => [
                'parent' => 'admin.staff',
                'description' => 'Unblock Staff',
            ],
            'admin.staff.role' => [
                'parent' => 'admin.staff',
                'description' => 'Manage Staff\'s Role',
            ],
            'admin.staff.role.list' => [
                'parent' => 'admin.staff.role',
                'description' => 'List of Role',
            ],
            'admin.staff.role.add' => [
                'parent' => 'admin.staff.role',
                'description' => 'Add Role',
            ],
            'admin.staff.role.update' => [
                'parent' => 'admin.staff.role',
                'description' => 'Update Role',
            ],
            'admin.staff.role.delete' => [
                'parent' => 'admin.staff.role',
                'description' => 'Delete Role',
            ],
            'admin.staff.role.permission' => [
                'parent' => 'admin.staff.role',
                'description' => 'Set Role\'s Permission',
            ],
        ];
    }

    public function installRole()
    {
        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        $adminRoot = $auth->createRole('role.admin.root');
        $adminRoot->description = 'Owner';

        if (!$auth->add($adminRoot)) {
            return false;
        }

        if (!$auth->addChild($adminRoot, $auth->getPermission('admin.root'))) {
            return false;
        }

        if (!$auth->assign($adminRoot, Staff::root()->account_id)) {
            return false;
        }

        $adminDeveloper = $auth->createRole('role.admin.developer');
        $adminDeveloper->description = "Developer";

        if (!$auth->add($adminDeveloper)) {
            return false;
        }

        if (!$auth->addChild($adminRoot, $adminDeveloper)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        if (!$auth->uninstallPermissions($this->permissions())) {
            return false;
        }

        if (!$auth->remove($auth->getRole('role.admin.root'))) {
            return false;
        }

        return true;
    }
}
