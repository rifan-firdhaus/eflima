<?php namespace modules\finance\migrations;

use modules\account\rbac\DbManager;
use modules\finance\models\ProposalStatus;
use Yii;
use yii\db\Migration;

/**
 * Class M201012135524Proposal
 */
class M201012135524Proposal extends Migration
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

        $this->createTable('{{%proposal_status}}', [
            'id' => $this->primaryKey()->unsigned(),
            'label' => $this->text()->notNull(),
            'color_label' => $this->char(7)->null(),
            'order' => $this->integer(3)->unsigned()->defaultValue(100),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'description' => $this->text()->null(),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%proposal}}', [
            'id' => $this->primaryKey()->unsigned(),
            'status_id' => $this->integer()->unsigned(),
            'currency_code' => $this->char(3)->notNull(),
            'number' => $this->text()->notNull(),
            'title' => $this->text()->notNull(),
            'model' => $this->text()->null(),
            'model_id' => $this->text()->null(),
            'date' => $this->integer()->unsigned(),
            'content' => $this->text()->null(),
            'currency_rate' => $this->decimal(25, 10)->defaultValue(0),
            'sub_total' => $this->decimal(25, 10)->defaultValue(0),
            'discount' => $this->decimal(25, 10)->defaultValue(0),
            'tax' => $this->decimal(25, 10)->defaultValue(0),
            'grand_total' => $this->decimal(25, 10)->defaultValue(0),
            'real_sub_total' => $this->decimal(25, 10)->defaultValue(0),
            'real_discount' => $this->decimal(25, 10)->defaultValue(0),
            'real_tax' => $this->decimal(25, 10)->defaultValue(0),
            'real_grand_total' => $this->decimal(25, 10)->defaultValue(0),
            'created_at' => $this->integer()->unsigned(),
            'creator_id' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
            'updater_id' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%proposal_item}}', [
            'id' => $this->primaryKey()->unsigned(),
            'proposal_id' => $this->integer()->unsigned()->notNull(),
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
            'order' => $this->smallInteger(5)->unsigned()->null()->defaultValue(0),
            'creator_id' => $this->integer()->unsigned(),
            'created_at' => $this->integer()->unsigned(),
            'updater_id' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%proposal_item_tax}}', [
            'id' => $this->primaryKey()->unsigned(),
            'tax_id' => $this->integer()->unsigned()->notNull(),
            'proposal_item_id' => $this->integer()->unsigned()->notNull(),
            'rate' => $this->decimal(8, 5)->defaultValue(0)->notNull(),
            'value' => $this->decimal(25, 10)->defaultValue(0)->notNull(),
            'real_value' => $this->decimal(25, 10)->defaultValue(0)->notNull(),
        ], $tableOptions);

        $this->createTable('{{%proposal_assignee}}', [
            'id' => $this->primaryKey()->unsigned(),
            'proposal_id' => $this->integer()->unsigned()->notNull(),
            'assignee_id' => $this->integer()->unsigned()->notNull(),
            'assigned_at' => $this->integer()->unsigned()->notNull(),
            'assignor_id' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'status_of_proposal',
            '{{%proposal}}', 'status_id',
            '{{%proposal_status}}', 'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'creator_of_proposal',
            '{{%proposal}}', 'creator_id',
            '{{%account}}', 'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'updater_of_proposal',
            '{{%proposal}}', 'updater_id',
            '{{%account}}', 'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'product_of_proposal_item',
            '{{%proposal_item}}', 'product_id',
            '{{%product}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'proposal_of_item',
            '{{%proposal_item}}', 'proposal_id',
            '{{%proposal}}', 'id'
        );

        $this->addForeignKey(
            'creator_of_proposal_item',
            '{{%proposal_item}}', 'creator_id',
            '{{%account}}', 'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'updater_of_proposal_item',
            '{{%proposal_item}}', 'updater_id',
            '{{%account}}', 'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'proposal_item_of_tax',
            '{{%proposal_item_tax}}', 'proposal_item_id',
            '{{%proposal_item}}', 'id'
        );

        $this->addForeignKey(
            'tax_of_proposal_item',
            '{{%proposal_item_tax}}', 'tax_id',
            '{{%tax}}', 'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'proposal_of_assignee',
            '{{%proposal_assignee}}', 'proposal_id',
            '{{%proposal}}', 'id'
        );

        $this->addForeignKey(
            'profile_of_proposal_assignee',
            '{{%proposal_assignee}}', 'assignee_id',
            '{{%staff}}', 'id'
        );

        $this->addForeignKey(
            'profile_of_proposal_assignor',
            '{{%proposal_assignee}}', 'assignor_id',
            '{{%staff}}', 'id'
        );

        $this->addForeignKey(
            'creator_of_proposal_status',
            '{{%proposal_status}}', 'creator_id',
            '{{%account}}', 'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'updater_of_proposal_status',
            '{{%proposal_status}}', 'updater_id',
            '{{%account}}', 'id',
            'SET NULL'
        );

        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        $time = time();
        $this->beginCommand('Register permissions');

        if (!$auth->installPermissions($this->permissions())) {
            return false;
        }

        $this->endCommand($time);

        $this->registerDefaults();
    }

    /**
     * @return bool
     */
    public function registerDefaults()
    {
        $statuses = [
            [
                'label' => Yii::t('app', 'Draft'),
                'color_label' => "#444444",
            ],
            [
                'label' => Yii::t('app', 'Open'),
                'color_label' => "#e83e8c",
            ],
            [
                'label' => Yii::t('app', 'Sent'),
                'color_label' => "#468bef",
            ],
            [
                'label' => Yii::t('app', 'Accepted'),
                'color_label' => "#28a745",
            ],
            [
                'label' => Yii::t('app', 'Declined'),
                'color_label' => "#dc3545",
            ],
        ];

        foreach ($statuses AS $index => $status) {
            $model = new ProposalStatus($status);
            $model->scenario = 'install';
            $model->is_enabled = true;
            $model->order = $index;

            if (!$model->save(false)) {
                return false;
            }
        }

        return true;
    }

    public function permissions()
    {
        return [
            'admin.proposal' => [
                'parent' => 'admin.root',
                'description' => 'Manage Proposal',
            ],
            'admin.proposal.list' => [
                'parent' => 'admin.proposal',
                'description' => 'List of Proposal',
            ],
            'admin.proposal.add' => [
                'parent' => 'admin.proposal',
                'description' => 'Add Proposal',
            ],
            'admin.proposal.update' => [
                'parent' => 'admin.proposal',
                'description' => 'Update Proposal',
            ],
            'admin.proposal.status' => [
                'parent' => 'admin.proposal',
                'description' => 'Update Proposal Status',
            ],
            'admin.proposal.view' => [
                'parent' => 'admin.proposal',
                'description' => 'View Propsal Details',
            ],
            'admin.proposal.view.detail' => [
                'parent' => 'admin.invoice.view',
                'description' => 'Proposal Detail',
            ],
            'admin.proposal.view.task' => [
                'parent' => 'admin.proposal.view',
                'description' => 'Proposal Task',
            ],
            'admin.proposal.view.history' => [
                'parent' => 'admin.proposal.view',
                'description' => 'Proposal History',
            ],
            'admin.proposal.history' => [
                'parent' => 'admin.proposal',
                'description' => 'All Proposal History',
            ],
            'admin.proposal.delete' => [
                'parent' => 'admin.proposal',
                'description' => 'Delete Proposal',
            ],

            'admin.proposal.item' => [
                'parent' => 'admin.proposal',
                'description' => 'Manage Proposal Items',
            ],
            'admin.proposal.item.add' => [
                'parent' => 'admin.proposal.item',
                'description' => 'Add Proposal Item',
            ],
            'admin.proposal.item.update' => [
                'parent' => 'admin.proposal.item',
                'description' => 'Update Proposal Item',
            ],
            'admin.proposal.item.delete' => [
                'parent' => 'admin.proposal.item',
                'description' => 'Delete Proposal Item',
            ],

            'admin.setting.finance.proposal-status' => [
                'parent' => 'admin.setting.finance',
                'description' => 'Proposal Status',
            ],
            'admin.setting.finance.proposal-status.list' => [
                'parent' => 'admin.setting.finance.proposal-status',
                'description' => 'List of Proposal Status',
            ],
            'admin.setting.finance.proposal-status.add' => [
                'parent' => 'admin.setting.finance.proposal-status',
                'description' => 'Add Proposal Status',
            ],
            'admin.setting.finance.proposal-status.update' => [
                'parent' => 'admin.setting.finance.proposal-status',
                'description' => 'Update Proposal Status',
            ],
            'admin.setting.finance.proposal-status.delete' => [
                'parent' => 'admin.setting.finance.proposal-status',
                'description' => 'Delete Proposal Status',
            ],
            'admin.setting.finance.proposal-status.visibility' => [
                'parent' => 'admin.setting.finance.proposal-status',
                'description' => 'Enable/Disable Proposal Status',
            ],
            'admin.customer.view.proposal' => [
                'parent' => 'admin.customer.view',
                'description' => 'Customer Proposal',
            ],
            'admin.lead.view.proposal' => [
                'parent' => 'admin.lead.view',
                'description' => 'Lead Proposal',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('status_of_proposal', '{{%proposal}}');
        $this->dropForeignKey('creator_of_proposal', '{{%proposal}}');
        $this->dropForeignKey('updater_of_proposal', '{{%proposal}}');

        $this->dropForeignKey('product_of_proposal_item', '{{%proposal_item}}');
        $this->dropForeignKey('proposal_of_item', '{{%proposal_item}}');
        $this->dropForeignKey('creator_of_proposal_item', '{{%proposal_item}}');
        $this->dropForeignKey('updater_of_proposal_item', '{{%proposal_item}}');

        $this->dropForeignKey('tax_of_proposal_item', '{{%proposal_item_tax}}');
        $this->dropForeignKey('proposal_item_of_tax', '{{%proposal_item_tax}}');

        $this->dropForeignKey('proposal_of_assignee', '{{%proposal_assignee}}');
        $this->dropForeignKey('profile_of_proposal_assignee', '{{%proposal_assignee}}');
        $this->dropForeignKey('profile_of_proposal_assignor', '{{%proposal_assignee}}');

        $this->dropForeignKey('creator_of_proposal_status', '{{%proposal_status}}');
        $this->dropForeignKey('updater_of_proposal_status', '{{%proposal_status}}');

        $this->dropTable('{{%proposal}}');
        $this->dropTable('{{%proposal_item}}');
        $this->dropTable('{{%proposal_item_tax}}');
        $this->dropTable('{{%proposal_status}}');
        $this->dropTable('{{%proposal_assignee}}');
    }
}
