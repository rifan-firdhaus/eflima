<?php

namespace modules\crm\migrations;

use yii\db\Migration;

/**
 * Class M190627123228Lead
 */
class M190322224234Lead extends Migration
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

        $this->createTable('{{%lead_source}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->text()->notNull(),
            'description' => $this->text()->null(),
            'color_label' => $this->text()->null(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%lead_status}}', [
            'id' => $this->primaryKey()->unsigned(),
            'label' => $this->text()->notNull(),
            'description' => $this->text()->null(),
            'color_label' => $this->text()->null(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%lead}}', [
            'id' => $this->primaryKey()->unsigned(),
            'customer_id' => $this->integer()->unsigned()->null(),
            'status_id' => $this->integer()->unsigned()->notNull(),
            'source_id' => $this->integer()->unsigned()->notNull(),
            'company' => $this->text()->null(),
            'first_name' => $this->text()->notNull(),
            'last_name' => $this->text()->null(),
            'phone' => $this->text()->null(),
            'email' => $this->text()->null(),
            'mobile' => $this->text()->null(),
            'city' => $this->text()->null(),
            'province' => $this->text()->null(),
            'country_code' => $this->char(3)->null(),
            'postal_code' => $this->text()->null(),
            'address' => $this->text()->null(),
            'order' => $this->integer()->unsigned()->defaultValue(99),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%lead_assignee}}', [
            'id' => $this->primaryKey()->unsigned(),
            'lead_id' => $this->integer()->unsigned()->notNull(),
            'assignee_id' => $this->integer()->unsigned()->notNull(),
            'assigned_at' => $this->integer()->unsigned()->notNull(),
            'assignor_id' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);

        $this->createTable('{{%lead_follow_up}}', [
            'id' => $this->primaryKey()->unsigned(),
            'date' => $this->integer()->unsigned()->notNull(),
            'lead_id' => $this->integer()->unsigned()->notNull(),
            'staff_id' => $this->integer()->unsigned()->notNull(),
            'type_id' => $this->integer()->unsigned()->notNull(),
            'duration' => $this->integer()->unsigned()->null(),
            'location' => $this->text()->null(),
            'note' => $this->text()->null(),
            'created_at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%lead_follow_up_type}}', [
            'id' => $this->primaryKey()->unsigned(),
            'label' => $this->text()->notNull(),
            'description' => $this->text()->null(),
            'is_enabled' => $this->boolean()->defaultValue(true),
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->addForeignKey(
            'status_of_lead',
            '{{%lead}}', 'status_id',
            '{{%lead_status}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'source_of_lead',
            '{{%lead}}', 'source_id',
            '{{%lead_source}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'lead_of_assignee',
            '{{%lead_assignee}}', 'lead_id',
            '{{%lead}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'assignee_profile',
            '{{%lead_assignee}}', 'assignee_id',
            '{{%staff}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'assignor_profile',
            '{{%lead_assignee}}', 'assignor_id',
            '{{%staff}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'type_of_follow_up',
            '{{%lead_follow_up}}', 'type_id',
            '{{%lead_follow_up_type}}', 'id',
            'CASCADE',
            'RESTRICT'
        );

        $this->addForeignKey(
            'lead_of_follow_up',
            '{{%lead_follow_up}}', 'lead_id',
            '{{%lead}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'staff_of_follow_up',
            '{{%lead_follow_up}}', 'staff_id',
            '{{%staff}}', 'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('status_of_lead', '{{%lead}}');
        $this->dropForeignKey('source_of_lead', '{{%lead}}');
        $this->dropForeignKey('lead_of_assignee', '{{%lead_assignee}}');
        $this->dropForeignKey('assignor_profile', '{{%lead_assignee}}');
        $this->dropForeignKey('assignee_profile', '{{%lead_assignee}}');
        $this->dropForeignKey('staff_of_follow_up', '{{%lead_follow_up}}');
        $this->dropForeignKey('lead_of_follow_up', '{{%lead_follow_up}}');
        $this->dropForeignKey('type_of_follow_up', '{{%lead_follow_up}}');

        $this->dropTable('{{%lead_status}}');
        $this->dropTable('{{%lead_source}}');
        $this->dropTable('{{%lead}}');
        $this->dropTable('{{%lead_assignee}}');
        $this->dropTable('{{%lead_follow_up}}');
        $this->dropTable('{{%lead_follow_up_type}}');
    }
}
