<?php namespace modules\core\migrations;

use modules\account\models\Staff;
use modules\account\rbac\DbManager;
use Yii;
use yii\db\Migration;

/**
 * Class M190413073345Rbac
 */
class M181001214220SettingPermisssion extends Migration
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

    /**
     * @return array
     */
    public function permissions()
    {
        return [
            'admin.setting' => [
                'parent' => 'admin.root',
                'description' => 'Manage Settings',
            ],
            'admin.setting.general' => [
                'parent' => 'admin.setting',
                'description' => 'General Settings',
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
