<?php

namespace modules\finance\migrations;

use yii\db\Migration;

/**
 * Class M190524083228Invoice
 */
class M190524083228Invoice extends Migration
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

        $this->createTable('{{%invoice}}', [
            'id' => $this->primaryKey()->unsigned(),
            'customer_id' => $this->integer()->unsigned(),
            'currency_code' => $this->char(3)->notNull(),
            'number' => $this->string(255)->notNull(),
            'status' => $this->char(1)->notNull(),
            'date' => $this->integer()->unsigned()->notNull(),
            'due_date' => $this->integer()->unsigned()->notNull(),
            'currency_rate' => $this->decimal(25, 10)->defaultValue(0),
            'sub_total' => $this->decimal(25, 10)->defaultValue(0),
            'discount' => $this->decimal(25, 10)->defaultValue(0),
            'tax' => $this->decimal(25, 10)->defaultValue(0),
            'grand_total' => $this->decimal(25, 10)->defaultValue(0),
            'total_paid' => $this->decimal(25, 10)->defaultValue(0),
            'total_due' => $this->decimal(25, 10)->defaultValue(0),
            'real_sub_total' => $this->decimal(25, 10)->defaultValue(0),
            'real_discount' => $this->decimal(25, 10)->defaultValue(0),
            'real_tax' => $this->decimal(25, 10)->defaultValue(0),
            'real_grand_total' => $this->decimal(25, 10)->defaultValue(0),
            'real_total_paid' => $this->decimal(25, 10)->defaultValue(0),
            'real_total_due' => $this->decimal(25, 10)->defaultValue(0),
            'is_assignee_allowed_to_add_payment' => $this->boolean()->defaultValue(1),
            'is_assignee_allowed_to_add_discount' => $this->boolean()->defaultValue(1),
            'is_assignee_allowed_to_update_item' => $this->boolean()->defaultValue(1),
            'is_assignee_allowed_to_cancel' => $this->boolean()->defaultValue(1),
            'is_published' => $this->boolean()->defaultValue(0),
            'is_paid' => $this->boolean()->defaultValue(0),
            'allowed_payment_method' => $this->text()->null(),
            'params' => $this->text()->null(),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%invoice_item}}', [
            'id' => $this->primaryKey()->unsigned(),
            'invoice_id' => $this->integer()->unsigned()->notNull(),
            'product_id' => $this->integer()->unsigned()->null(),
            'name' => $this->text(),
            'picture' => $this->text()->null(),
            'type' => $this->string(64)->notNull(),
            'price' => $this->decimal(25, 10)->defaultValue(0),
            'real_price' => $this->decimal(25, 10)->defaultValue(0),
            'amount' => $this->decimal(25, 10)->defaultValue(0),
            'tax' => $this->decimal(25, 10)->defaultValue(0),
            'real_tax' => $this->decimal(25, 10)->defaultValue(0),
            'sub_total' => $this->decimal(25, 10)->defaultValue(0),
            'real_sub_total' => $this->decimal(25, 10)->defaultValue(0),
            'grand_total' => $this->decimal(25, 10)->defaultValue(0),
            'real_grand_total' => $this->decimal(25, 10)->defaultValue(0),
            'params' => $this->text()->null(),
            'terms' => $this->text()->null(),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%invoice_item_tax}}', [
            'id' => $this->primaryKey()->unsigned(),
            'tax_id' => $this->integer()->unsigned()->notNull(),
            'invoice_item_id' => $this->integer()->unsigned()->notNull(),
            'rate' => $this->decimal(8, 5)->defaultValue(0)->notNull(),
            'value' => $this->decimal(25, 10)->defaultValue(0)->notNull(),
            'real_value' => $this->decimal(25, 10)->defaultValue(0)->notNull(),
        ], $tableOptions);

        $this->createTable('{{%invoice_payment_schedule}}', [
            'id' => $this->primaryKey()->unsigned(),
            'invoice_id' => $this->integer()->unsigned()->notNull(),
            'type' => $this->char(1)->notNull(),
            'fixed_amount' => $this->decimal(25, 10)->null(),
            'percent' => $this->decimal(6, 3)->null(),
            'date' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%invoice_payment}}', [
            'id' => $this->primaryKey()->unsigned(),
            'number' => $this->text()->notNull(),
            'invoice_id' => $this->integer()->unsigned()->notNull(),
            'method_id' => $this->string(128)->notNull(),
            'amount' => $this->decimal(25, 10)->defaultValue(0),
            'real_amount' => $this->decimal(25, 10)->defaultValue(0),
            'status' => $this->char(1)->notNull(),
            'data' => $this->text()->null(),
            'note' => $this->text()->null(),
            'is_manual' => $this->boolean()->defaultValue(0),
            'accepted_at' => $this->integer()->unsigned()->null(),
            'at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%invoice_assignee}}', [
            'id' => $this->primaryKey()->unsigned(),
            'invoice_id' => $this->integer()->unsigned()->notNull(),
            'assignee_id' => $this->integer()->unsigned()->notNull(),
            'assigned_at' => $this->integer()->unsigned()->notNull(),
            'assignor_id' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'customer_of_invoice',
            '{{%invoice}}', 'customer_id',
            '{{%customer}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'product_of_item',
            '{{%invoice_item}}', 'product_id',
            '{{%product}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'invoice_of_item',
            '{{%invoice_item}}', 'invoice_id',
            '{{%invoice}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'invoice_item_of_tax',
            '{{%invoice_item_tax}}', 'invoice_item_id',
            '{{%invoice_item}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'tax_of_invoice_item',
            '{{%invoice_item_tax}}', 'tax_id',
            '{{%tax}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'invoice_of_payment_schedule',
            '{{%invoice_payment_schedule}}', 'invoice_id',
            '{{%invoice}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'invoice_of_payment',
            '{{%invoice_payment}}', 'invoice_id',
            '{{%invoice}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'invoice_of_assignee',
            '{{%invoice_assignee}}', 'invoice_id',
            '{{%invoice}}', 'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('customer_of_invoice', '{{%invoice}}');
        $this->dropForeignKey('invoice_of_item', '{{%invoice_item}}');
        $this->dropForeignKey('product_of_item', '{{%invoice_item}}');
        $this->dropForeignKey('invoice_item_of_tax', '{{%invoice_item_tax}}');
        $this->dropForeignKey('tax_of_invoice_item', '{{%invoice_item_tax}}');
        $this->dropForeignKey('invoice_of_payment_schedule', '{{%invoice_payment_schedule}}');
        $this->dropForeignKey('invoice_of_payment', '{{%invoice_payment}}');
        $this->dropForeignKey('invoice_of_assignee', '{{%invoice_assignee}}');

        $this->dropTable('{{%invoice}}');
        $this->dropTable('{{%invoice_item_tax}}');
        $this->dropTable('{{%invoice_item}}');
        $this->dropTable('{{%invoice_payment_schedule}}');
        $this->dropTable('{{%invoice_payment}}');
        $this->dropTable('{{%invoice_assignee}}');
    }
}
