<?php

namespace modules\finance\migrations;

use modules\account\rbac\DbManager;
use Yii;
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
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
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
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%expense_tax}}', [
            'id' => $this->primaryKey()->unsigned(),
            'tax_id' => $this->integer()->unsigned()->notNull(),
            'expense_id' => $this->integer()->unsigned()->notNull(),
            'rate' => $this->decimal(8, 5)->defaultValue(0)->notNull(),
            'value' => $this->decimal(25, 10)->defaultValue(0)->notNull(),
            'real_value' => $this->decimal(25, 10)->defaultValue(0)->notNull(),
        ], $tableOptions);

        $this->createTable('{{%expense_attachment}}', [
            'id' => $this->primaryKey()->unsigned(),
            'expense_id' => $this->integer()->unsigned()->notNull(),
            'file' => $this->text()->notNull(),
            'uploaded_at' => $this->integer()->unsigned()->notNull(),
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
            'creator_of_expense',
            '{{%expense}}', 'creator_id',
            '{{%account}}', 'id',
            'SET NULL'
        );
        $this->addForeignKey(
            'updater_of_expense',
            '{{%expense}}', 'updater_id',
            '{{%account}}', 'id',
            'SET NULL'
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
            'admin.expense' => [
                'parent' => 'admin.root',
                'description' => 'Manage Expense',
            ],
            'admin.expense.list' => [
                'parent' => 'admin.expense',
                'description' => 'List of Expense',
            ],
            'admin.expense.add' => [
                'parent' => 'admin.expense',
                'description' => 'Add Expense',
            ],
            'admin.expense.update' => [
                'parent' => 'admin.expense',
                'description' => 'Update Expense',
            ],
            'admin.expense.view' => [
                'parent' => 'admin.expense',
                'description' => 'View Expense Details',
            ],
            'admin.expense.view.detail' => [
                'parent' => 'admin.expense.view',
                'description' => 'Expense Details',
            ],
            'admin.expense.view.history' => [
                'parent' => 'admin.expense.view',
                'description' => 'Expense History',
            ],
            'admin.expense.view.task' => [
                'parent' => 'admin.expense.view',
                'description' => 'Expense Task',
            ],
            'admin.expense.delete' => [
                'parent' => 'admin.expense',
                'description' => 'Delete Expense',
            ],
            'admin.expense.bill' => [
                'parent' => 'admin.expense',
                'description' => 'Bill Expense to Invoice',
            ],

            'admin.setting.finance.expense-category' => [
                'parent' => 'admin.setting.finance',
                'description' => 'Expense Category',
            ],
            'admin.setting.finance.expense-category.list' => [
                'parent' => 'admin.setting.finance.expense-category',
                'description' => 'List of Expense Category',
            ],
            'admin.setting.finance.expense-category.add' => [
                'parent' => 'admin.setting.finance.expense-category',
                'description' => 'Add Expense Category',
            ],
            'admin.setting.finance.expense-category.update' => [
                'parent' => 'admin.setting.finance.expense-category',
                'description' => 'Update Expense Category',
            ],
            'admin.setting.finance.expense-category.delete' => [
                'parent' => 'admin.setting.finance.expense-category',
                'description' => 'Delete Expense Category',
            ],
            'admin.setting.finance.expense-category.visibility' => [
                'parent' => 'admin.setting.finance.expense-category',
                'description' => 'Enable/Disable Expense Category',
            ],

            'admin.customer.view.expense' => [
                'parent' => 'admin.customer.view',
                'description' => 'Customer Expense'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('category_of_expense', '{{%expense}}');
        $this->dropForeignKey('customer_of_expense', '{{%expense}}');
        $this->dropForeignKey('creator_of_expense', '{{%expense}}');
        $this->dropForeignKey('updater_of_expense', '{{%expense}}');

        $this->dropForeignKey('tax_of_expense', '{{%expense_tax}}');
        $this->dropForeignKey('detail', '{{%expense_tax}}');

        $this->dropForeignKey('expense_of_attachment', '{{%expense_attachment}}');

        $this->dropTable('{{%expense_category}}');
        $this->dropTable('{{%expense}}');
        $this->dropTable('{{%expense_tax}}');
        $this->dropTable('{{%expense_attachment}}');

        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        if (!$auth->uninstallPermissions($this->permissions())) {
            return false;
        }

        return true;
    }
}
