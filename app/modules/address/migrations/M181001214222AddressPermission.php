<?php namespace modules\address\migrations;

use modules\account\rbac\DbManager;
use Yii;
use yii\db\Migration;

/**
 * Class M190325214217Address
 */
class M181001214222AddressPermission extends Migration
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
    }

    public function permissions()
    {
        return [
            'admin.setting.country' => [
                'parent' => 'admin.setting',
                'description' => 'Manage Country',
            ],
            'admin.setting.country.list' => [
                'parent' => 'admin.setting.country',
                'description' => 'List of Country',
            ],
            'admin.setting.country.add' => [
                'parent' => 'admin.setting.country',
                'description' => 'Add Country',
            ],
            'admin.setting.country.update' => [
                'parent' => 'admin.setting.country',
                'description' => 'Update Country',
            ],
            'admin.setting.country.delete' => [
                'parent' => 'admin.setting.country',
                'description' => 'Delete Country',
            ],
            'admin.setting.country.visibility' => [
                'parent' => 'admin.setting.country',
                'description' => 'Enable/Disable Country',
            ],

            'admin.setting.city' => [
                'parent' => 'admin.setting',
                'description' => 'Manage City',
            ],
            'admin.setting.city.list' => [
                'parent' => 'admin.setting.city',
                'description' => 'List of City',
            ],
            'admin.setting.city.add' => [
                'parent' => 'admin.setting.city',
                'description' => 'Add City',
            ],
            'admin.setting.city.update' => [
                'parent' => 'admin.setting.city',
                'description' => 'Update City',
            ],
            'admin.setting.city.delete' => [
                'parent' => 'admin.setting.city',
                'description' => 'Delete City',
            ],
            'admin.setting.city.visibility' => [
                'parent' => 'admin.setting.city',
                'description' => 'Enable/Disable City',
            ],

            'admin.setting.province' => [
                'parent' => 'admin.setting',
                'description' => 'Manage Province',
            ],
            'admin.setting.province.list' => [
                'parent' => 'admin.setting.province',
                'description' => 'List of Province',
            ],
            'admin.setting.province.add' => [
                'parent' => 'admin.setting.province',
                'description' => 'Add Province',
            ],
            'admin.setting.province.update' => [
                'parent' => 'admin.setting.province',
                'description' => 'Update Province',
            ],
            'admin.setting.province.delete' => [
                'parent' => 'admin.setting.province',
                'description' => 'Delete Province',
            ],
            'admin.setting.province.visibility' => [
                'parent' => 'admin.setting.province',
                'description' => 'Enable/Disable Province',
            ],
        ];
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

        return true;
    }
}
