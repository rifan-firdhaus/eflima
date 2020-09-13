<?php

namespace modules\finance\migrations;

use yii\db\Migration;

/**
 * Class M190405102805Expense
 */
class M190405102805Expense extends Migration
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

        $this->createTable('{{%expense_category}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->text()->notNull(),
            'description' => $this->text()->null(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%expense}}', [
            'id' => $this->primaryKey()->unsigned(),
            'category_id' => $this->integer()->unsigned(),
            'customer_id' => $this->integer()->unsigned()->null(),
            'invoice_item_id' => $this->integer()->unsigned()->null(),
            'currency_code' => $this->char(3)->notNull(),
            'date' => $this->integer()->unsigned(),
            'reference' => $this->text()->null(),
            'name' => $this->text()->null(),
            'currency_rate' => $this->decimal(25, 10)->defaultValue(1),
            'amount' => $this->decimal(25, 10)->defaultValue(0),
            'tax' => $this->decimal(25, 10)->defaultValue(0),
            'total' => $this->decimal(25, 10)->defaultValue(0),
            'real_total' => $this->decimal(25, 10)->defaultValue(0),
            'real_tax' => $this->decimal(25, 10)->defaultValue(0),
            'real_amount' => $this->decimal(25, 10)->defaultValue(0),
            'description' => $this->text()->null(),
            'is_tax_included' => $this->boolean()->defaultValue(0),
            'is_billable' => $this->boolean()->defaultValue(0),
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%expense_tax}}', [
            'id' => $this->primaryKey()->unsigned(),
            'tax_id' => $this->integer()->unsigned()->notNull(),
            'expense_id' => $this->integer()->unsigned()->notNull(),
            'rate' => $this->decimal(8, 5)->defaultValue(0)->notNull(),
            'value' => $this->decimal(25, 10)->defaultValue(0)->notNull(),
            'real_value' => $this->decimal(25, 10)->defaultValue(0)->notNull()
        ], $tableOptions);

        $this->createTable('{{%expense_attachment}}', [
            'id' => $this->primaryKey()->unsigned(),
            'expense_id' => $this->integer()->unsigned()->notNull(),
            'file' => $this->text()->notNull(),
            'uploaded_at' => $this->integer()->unsigned()->notNull()
        ], $tableOptions);

        $this->addForeignKey(
            'category_of_expense',
            '{{%expense}}', 'category_id',
            '{{%expense_category}}', 'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'customer_of_expense',
            '{{%expense}}', 'customer_id',
            '{{%customer}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'tax_of_expense',
            '{{%expense_tax}}', 'tax_id',
            '{{%tax}}', 'id',
            'RESTRICT',
            'CASCADE'
        );
        $this->addForeignKey(
            'detail',
            '{{%expense_tax}}', 'expense_id',
            '{{%expense}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'expense_of_attachment',
            '{{%expense_attachment}}', 'expense_id',
            '{{%expense}}', 'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('category_of_expense', '{{%expense}}');
        $this->dropForeignKey('customer_of_expense', '{{%expense}}');

        $this->dropForeignKey('tax_of_expense', '{{%expense_tax}}');
        $this->dropForeignKey('detail', '{{%expense_tax}}');

        $this->dropForeignKey('expense_of_attachment', '{{%expense_attachment}}');

        $this->dropTable('{{%expense_category}}');
        $this->dropTable('{{%expense}}');
        $this->dropTable('{{%expense_tax}}');
        $this->dropTable('{{%expense_attachment}}');

        return true;
    }
}
