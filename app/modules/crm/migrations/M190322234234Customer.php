<?php namespace modules\crm\migrations;

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
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ],$tableOptions);

        $this->createTable('{{%customer}}', [
            'id' => $this->primaryKey()->unsigned(),
            'group_id' => $this->integer()->unsigned()->null(),
            'lead_id' => $this->integer()->unsigned()->null(),
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
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ],$tableOptions);

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
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ],$tableOptions);

        $this->addForeignKey(
            'group_of_customer',
            '{{%customer}}', 'group_id',
            '{{%customer_group}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'customer_of_contact',
            '{{%customer_contact}}', 'customer_id',
            '{{%customer}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'account_of_contact',
            '{{%customer_contact}}','account_id',
            '{{%account}}','id',
            'RESTRICT',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('group_of_customer', '{{%customer}}');

        $this->dropForeignKey('customer_of_contact', '{{%customer_contact}}');
        $this->dropForeignKey('account_of_contact', '{{%customer_contact}}');

        $this->dropTable('{{%customer_group}}');
        $this->dropTable('{{%customer}}');
        $this->dropTable('{{%customer_contact}}');

        return true;
    }
}
