<?php

namespace modules\crm\migrations;

use modules\account\rbac\DbManager;
use modules\core\db\MigrationSettingInstaller;
use modules\crm\models\LeadSource;
use modules\crm\models\LeadStatus;
use Yii;
use yii\db\Migration;

/**
 * Class M190627123228Lead
 */
class M190322224234Lead extends Migration
{
    use MigrationSettingInstaller;

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
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%lead_status}}', [
            'id' => $this->primaryKey()->unsigned(),
            'label' => $this->text()->notNull(),
            'description' => $this->text()->null(),
            'color_label' => $this->text()->null(),
            'order' => $this->integer(3)->unsigned()->defaultValue(100),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
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
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
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
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->addForeignKey(
            'creator_of_lead_source',
            '{{%lead_source}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_lead_source',
            '{{%lead_source}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'creator_of_lead_status',
            '{{%lead_status}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_lead_status',
            '{{%lead_status}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'creator_of_lead_follow_up_type',
            '{{%lead_follow_up_type}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_lead_follow_up_type',
            '{{%lead_follow_up_type}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

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
            'creator_of_lead',
            '{{%lead}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_lead',
            '{{%lead}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'lead_of_assignee',
            '{{%lead_assignee}}', 'lead_id',
            '{{%lead}}', 'id'
        );

        $this->addForeignKey(
            'assignee_profile',
            '{{%lead_assignee}}', 'assignee_id',
            '{{%staff}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'assignor_profile',
            '{{%lead_assignee}}', 'assignor_id',
            '{{%staff}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'type_of_follow_up',
            '{{%lead_follow_up}}', 'type_id',
            '{{%lead_follow_up_type}}', 'id'
        );

        $this->addForeignKey(
            'lead_of_follow_up',
            '{{%lead_follow_up}}', 'lead_id',
            '{{%lead}}', 'id'
        );

        $this->addForeignKey(
            'staff_of_follow_up',
            '{{%lead_follow_up}}', 'staff_id',
            '{{%staff}}', 'id'
        );

        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        $time = time();
        $this->beginCommand('Register permissions');

        if (!$auth->installPermissions($this->permissions())) {
            return false;
        }

        $this->endCommand($time);

        $this->registerSettings();
        $this->registerDefaults();
    }

    /**
     * @return bool
     */
    public function registerDefaults()
    {
        $statuses = [
            [
                'label' => Yii::t('app', 'New'),
                'color_label' => "#e83e8c",
            ],
            [
                'label' => Yii::t('app', 'Qualified'),
                'color_label' => "#ff9800",
            ],
            [
                'label' => Yii::t('app', 'Not Qualified'),
                'color_label' => "#dc3545",
            ],
            [
                'label' => Yii::t('app', 'Converted'),
                'color_label' => "#28a745",
            ],
        ];
        $source = [
            [
                'name' => Yii::t('app', 'Newsletter'),
                'color_label' => "#e83e8c",
            ],
            [
                'name' => Yii::t('app', 'Online Ads'),
                'color_label' => "#ff9800",
            ],
            [
                'name' => Yii::t('app', 'Offline Ads'),
                'color_label' => "#dc3545",
            ],
        ];

        foreach ($statuses AS $index => $status) {
            $model = new LeadStatus($status);
            $model->scenario = 'install';
            $model->is_enabled = true;
            $model->order = $index;

            if (!$model->save(false)) {
                return false;
            }
        }

        foreach ($source AS $index => $status) {
            $model = new LeadSource($status);
            $model->scenario = 'install';
            $model->is_enabled = true;

            if (!$model->save(false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function settings()
    {
        return [
            [
                'id' => 'lead/converted_status',
                'value' => 1,
            ],
            [
                'id' => 'lead/default_status',
                'value' => 1,
            ],
        ];
    }

    /**
     * @return array
     */
    public function permissions()
    {
        return [
            'admin.lead' => [
                'parent' => 'admin.root',
                'description' => 'Manage Lead',
            ],
            'admin.lead.list' => [
                'parent' => 'admin.lead',
                'description' => 'List of Lead',
            ],
            'admin.lead.kanban' => [
                'parent' => 'admin.lead',
                'description' => 'Lead Kanban View',
            ],
            'admin.lead.add' => [
                'parent' => 'admin.lead',
                'description' => 'Add Lead',
            ],
            'admin.lead.update' => [
                'parent' => 'admin.lead',
                'description' => 'Update Lead',
            ],
            'admin.lead.status' => [
                'parent' => 'admin.lead',
                'description' => 'Update Lead Status',
            ],
            'admin.lead.assignee' => [
                'parent' => 'admin.lead',
                'description' => 'Assign Staff to Lead',
            ],
            'admin.lead.view' => [
                'parent' => 'admin.lead',
                'description' => 'View Lead Details',
            ],
            'admin.lead.view.detail' => [
                'parent' => 'admin.lead.view',
                'description' => 'Lead Details',
            ],
            'admin.lead.view.task' => [
                'parent' => 'admin.lead.view',
                'description' => 'Lead Task',
            ],
            'admin.lead.view.event' => [
                'parent' => 'admin.lead.view',
                'description' => 'Lead Event',
            ],
            'admin.lead.view.history' => [
                'parent' => 'admin.lead.view',
                'description' => 'Lead History',
            ],
            'admin.lead.follow-up' => [
                'parent' => 'admin.lead',
                'description' => 'Follow Up Lead',
            ],
            'admin.lead.follow-up.list' => [
                'parent' => 'admin.lead.follow-up',
                'description' => 'List of Follow Up',
            ],
            'admin.lead.follow-up.add' => [
                'parent' => 'admin.lead.follow-up',
                'description' => 'Add Follow Up',
            ],
            'admin.lead.follow-up.update' => [
                'parent' => 'admin.lead.follow-up',
                'description' => 'Update Follow Up',
            ],
            'admin.lead.follow-up.delete' => [
                'parent' => 'admin.lead.follow-up',
                'description' => 'Delete Follow Up',
            ],
            'admin.lead.delete' => [
                'parent' => 'admin.lead',
                'description' => 'Delete Lead',
            ],
            'admin.lead.history' => [
                'parent' => 'admin.lead',
                'description' => 'View All Lead History',
            ],

            'admin.setting.crm' => [
                'parent' => 'admin.setting',
                'description' => 'CRM Setting',
            ],
            'admin.setting.crm.general' => [
                'parent' => 'admin.setting.crm',
                'description' => 'CRM General Setting',
            ],

            'admin.setting.crm.lead-source' => [
                'parent' => 'admin.setting.crm',
                'description' => 'Lead Source',
            ],
            'admin.setting.crm.lead-source.list' => [
                'parent' => 'admin.setting.crm.lead-source',
                'description' => 'List of Lead Source',
            ],
            'admin.setting.crm.lead-source.add' => [
                'parent' => 'admin.setting.crm.lead-source',
                'description' => 'Add Lead Source',
            ],
            'admin.setting.crm.lead-source.update' => [
                'parent' => 'admin.setting.crm.lead-source',
                'description' => 'Update Lead Source',
            ],
            'admin.setting.crm.lead-source.delete' => [
                'parent' => 'admin.setting.crm.lead-source',
                'description' => 'Delete Lead Source',
            ],
            'admin.setting.crm.lead-source.visibility' => [
                'parent' => 'admin.setting.crm.lead-source',
                'description' => 'Enable/Disable Lead Source',
            ],

            'admin.setting.crm.lead-follow-up-type' => [
                'parent' => 'admin.setting.crm',
                'description' => 'Lead Follow Up Type',
            ],
            'admin.setting.crm.lead-follow-up-type.list' => [
                'parent' => 'admin.setting.crm.lead-follow-up-type',
                'description' => 'List of Lead Follow Up Type',
            ],
            'admin.setting.crm.lead-follow-up-type.add' => [
                'parent' => 'admin.setting.crm.lead-follow-up-type',
                'description' => 'Add Lead Follow Up Type',
            ],
            'admin.setting.crm.lead-follow-up-type.update' => [
                'parent' => 'admin.setting.crm.lead-follow-up-type',
                'description' => 'Update Lead Follow Up Type',
            ],
            'admin.setting.crm.lead-follow-up-type.delete' => [
                'parent' => 'admin.setting.crm.lead-follow-up-type',
                'description' => 'Delete Lead Follow Up Type',
            ],
            'admin.setting.crm.lead-follow-up-type.visibility' => [
                'parent' => 'admin.setting.crm.lead-follow-up-type',
                'description' => 'Enable/Disable Lead Follow Up Type',
            ],

            'admin.setting.crm.lead-status' => [
                'parent' => 'admin.setting.crm',
                'description' => 'Lead Status',
            ],
            'admin.setting.crm.lead-status.list' => [
                'parent' => 'admin.setting.crm.lead-status',
                'description' => 'List of Lead Status',
            ],
            'admin.setting.crm.lead-status.add' => [
                'parent' => 'admin.setting.crm.lead-status',
                'description' => 'Add Lead Status',
            ],
            'admin.setting.crm.lead-status.update' => [
                'parent' => 'admin.setting.crm.lead-status',
                'description' => 'Update Lead Status',
            ],
            'admin.setting.crm.lead-status.delete' => [
                'parent' => 'admin.setting.crm.lead-status',
                'description' => 'Delete Lead Status',
            ],
            'admin.setting.crm.lead-status.visibility' => [
                'parent' => 'admin.setting.crm.lead-status',
                'description' => 'Enable/Disable Lead Status',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('creator_of_lead_source', '{{%lead_source}}');
        $this->dropForeignKey('updater_of_lead_source', '{{%lead_source}}');

        $this->dropForeignKey('creator_of_lead_follow_up_type', '{{%lead_follow_up_type}}');
        $this->dropForeignKey('updater_of_lead_follow_up_type', '{{%lead_follow_up_type}}');

        $this->dropForeignKey('creator_of_lead_status', '{{%lead_status}}');
        $this->dropForeignKey('updater_of_lead_status', '{{%lead_status}}');

        $this->dropForeignKey('status_of_lead', '{{%lead}}');
        $this->dropForeignKey('source_of_lead', '{{%lead}}');
        $this->dropForeignKey('creator_of_lead', '{{%lead}}');
        $this->dropForeignKey('updater_of_lead', '{{%lead}}');

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

        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        if (!$auth->uninstallPermissions($this->permissions())) {
            return false;
        }
    }
}
