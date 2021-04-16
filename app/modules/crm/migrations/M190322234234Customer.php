<?php namespace modules\crm\migrations;

use Faker\Factory;
use modules\account\rbac\DbManager;
use modules\address\models\Country;
use modules\crm\models\Customer;
use modules\crm\models\CustomerContact;
use modules\crm\models\CustomerContactAccount;
use Yii;
use yii\db\Migration;

/**
 * Class M190322234234Customer
 */
class M190322234234Customer extends Migration
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

        $this->createTable('{{%customer_group}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->text()->notNull(),
            'color_label' => $this->text()->null(),
            'description' => $this->text()->null(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%customer}}', [
            'id' => $this->primaryKey()->unsigned(),
            'group_id' => $this->integer()->unsigned()->null(),
            'customer_id' => $this->integer()->unsigned()->null(),
            'company_name' => $this->text()->null(),
            'company_logo' => $this->text()->null(),
            'vat_number' => $this->text()->null(),
            'city' => $this->text()->null(),
            'province' => $this->text()->null(),
            'country_code' => $this->char(3)->null(),
            'address' => $this->text()->null(),
            'type' => $this->char(1)->notNull(),
            'phone' => $this->text()->null(),
            'postal_code' => $this->text()->null(),
            'fax' => $this->text()->null(),
            'email' => $this->text()->null(),
            'is_archieved' => $this->boolean()->defaultValue(0),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%customer_contact}}', [
            'id' => $this->primaryKey()->unsigned(),
            'customer_id' => $this->integer()->unsigned(),
            'account_id' => $this->integer()->unsigned()->null(),
            'first_name' => $this->text()->null(),
            'last_name' => $this->text()->null(),
            'is_primary' => $this->boolean()->defaultValue(0),
            'has_customer_area_access' => $this->boolean()->defaultValue(1),
            'phone' => $this->text()->null(),
            'email' => $this->text()->null(),
            'mobile' => $this->text()->null(),
            'city' => $this->text()->null(),
            'province' => $this->text()->null(),
            'country_code' => $this->char(3)->null(),
            'postal_code' => $this->text()->null(),
            'address' => $this->text()->null(),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->addForeignKey(
            'creator_of_customer_group',
            '{{%customer_group}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_customer_group',
            '{{%customer_group}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'group_of_customer',
            '{{%customer}}', 'group_id',
            '{{%customer_group}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'creator_of_customer',
            '{{%customer}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_customer',
            '{{%customer}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'customer_of_contact',
            '{{%customer_contact}}', 'customer_id',
            '{{%customer}}', 'id'
        );

        $this->addForeignKey(
            'creator_of_customer_contact',
            '{{%customer_contact}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_customer_contact',
            '{{%customer_contact}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'account_of_contact',
            '{{%customer_contact}}', 'account_id',
            '{{%account}}', 'id'
        );

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
            'admin.customer' => [
                'parent' => 'admin.root',
                'description' => 'Manage Customer',
            ],
            'admin.customer.list' => [
                'parent' => 'admin.customer',
                'description' => 'List of Customer',
            ],
            'admin.customer.add' => [
                'parent' => 'admin.customer',
                'description' => 'Add Customer',
            ],
            'admin.customer.update' => [
                'parent' => 'admin.customer',
                'description' => 'Update Customer',
            ],
            'admin.customer.view' => [
                'parent' => 'admin.customer',
                'description' => 'View Customer Details',
            ],
            'admin.customer.view.detail' => [
                'parent' => 'admin.customer.view',
                'description' => 'Customer Detail',
            ],
            'admin.customer.view.contact' => [
                'parent' => 'admin.customer.view',
                'description' => 'Customer Contact',
            ],
            'admin.customer.view.task' => [
                'parent' => 'admin.customer.view',
                'description' => 'Customer Task',
            ],
            'admin.customer.view.event' => [
                'parent' => 'admin.customer.view',
                'description' => 'Customer Event',
            ],
            'admin.customer.view.history' => [
                'parent' => 'admin.customer.view',
                'description' => 'Customer History',
            ],
            'admin.customer.delete' => [
                'parent' => 'admin.customer',
                'description' => 'Delete Customer',
            ],

            'admin.customer.contact' => [
                'parent' => 'admin.customer',
                'description' => 'Manage Contact',
            ],
            'admin.customer.contact.list' => [
                'parent' => 'admin.customer.contact',
                'description' => 'List of Contact',
            ],
            'admin.customer.contact.add' => [
                'parent' => 'admin.customer.contact',
                'description' => 'Add Contact',
            ],
            'admin.customer.contact.update' => [
                'parent' => 'admin.customer.contact',
                'description' => 'Update Contact',
            ],
            'admin.customer.contact.delete' => [
                'parent' => 'admin.customer.contact',
                'description' => 'Delete Contact',
            ],

            'admin.setting.crm.customer-group' => [
                'parent' => 'admin.setting.crm',
                'description' => 'Customer Group',
            ],
            'admin.setting.crm.customer-group.list' => [
                'parent' => 'admin.setting.crm.customer-group',
                'description' => 'List of Customer Group',
            ],
            'admin.setting.crm.customer-group.add' => [
                'parent' => 'admin.setting.crm.customer-group',
                'description' => 'Add Customer Group',
            ],
            'admin.setting.crm.customer-group.update' => [
                'parent' => 'admin.setting.crm.customer-group',
                'description' => 'Update Customer Group',
            ],
            'admin.setting.crm.customer-group.delete' => [
                'parent' => 'admin.setting.crm.customer-group',
                'description' => 'Delete Customer Group',
            ],
            'admin.setting.crm.customer-group.visibility' => [
                'parent' => 'admin.setting.crm.customer-group',
                'description' => 'Enable/Disable Customer Group',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('creator_of_customer_group', '{{%customer_group}}');
        $this->dropForeignKey('updater_of_customer_group', '{{%customer_group}}');

        $this->dropForeignKey('group_of_customer', '{{%customer}}');
        $this->dropForeignKey('creator_of_customer', '{{%customer}}');
        $this->dropForeignKey('updater_of_customer', '{{%customer}}');

        $this->dropForeignKey('customer_of_contact', '{{%customer_contact}}');
        $this->dropForeignKey('account_of_contact', '{{%customer_contact}}');
        $this->dropForeignKey('creator_of_customer_contact', '{{%customer_contact}}');
        $this->dropForeignKey('updater_of_customer_contact', '{{%customer_contact}}');

        $this->dropTable('{{%customer_group}}');
        $this->dropTable('{{%customer}}');
        $this->dropTable('{{%customer_contact}}');

        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        if (!$auth->uninstallPermissions($this->permissions())) {
            return false;
        }

        return true;
    }
}
