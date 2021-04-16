<?php namespace modules\account\migrations;

use modules\account\models\AccountContact;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use yii\db\Migration;

/**
 * Class M190306202904Staff
 */
class M181001214218Staff extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%staff}}', [
            'id' => $this->primaryKey()->unsigned(),
            'account_id' => $this->integer()->unsigned()->notNull(),
            'first_name' => $this->text()->null(),
            'last_name' => $this->text()->null(),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->addForeignKey(
            'staff_account',
            '{{%staff}}', 'account_id',
            '{{%account}}', 'id'
        );

        $this->addForeignKey(
            'creator_of_staff',
            '{{%staff}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_staff',
            '{{%staff}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->defaultAdmin();
    }

    public function defaultAdmin()
    {
        $time = microtime(true);
        $this->beginCommand('Register root admin');

        $staff = new Staff([
            'scenario' => 'install',
            'first_name' => 'Rifan',
            'last_name' => 'Firdhaus',
        ]);

        $staff->accountModel = new StaffAccount([
            'scenario' => 'install',
            'username' => 'admin',
            'password' => 'rifan1234',
            'password_repeat' => 'rifan1234',
            'email' => 'rifanfirdhaus@gmail.com',
        ]);

        $staff->accountModel->contactModel = new AccountContact([
            'scenario' => 'install',
            'phone' => '+6285649303689',
        ]);

        $isSuccess = $staff->save();

        $this->endCommand($time);

        return $isSuccess;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('staff_account', '{{%staff}}');
        $this->dropForeignKey('updater_of_staff', '{{%staff}}');
        $this->dropForeignKey('creator_of_staff', '{{%staff}}');

        $this->dropTable('{{%staff}}');

        return true;
    }
}
