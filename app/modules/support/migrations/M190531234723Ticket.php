<?php

namespace modules\support\migrations;

use modules\account\rbac\DbManager;
use modules\core\components\Setting;
use modules\support\models\TicketDepartment;
use modules\support\models\TicketPriority;
use modules\support\models\TicketStatus;
use Yii;
use yii\db\Migration;

/**
 * Class M190604014021Ticket
 */
class M190531234723Ticket extends Migration
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

        $this->createTable('{{%ticket_status}}', [
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

        $this->createTable('{{%ticket_department}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->text()->notNull(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'description' => $this->text()->null(),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%ticket_priority}}', [
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

        $this->createTable('{{%ticket}}', [
            'id' => $this->primaryKey()->unsigned(),
            'priority_id' => $this->integer()->unsigned()->notNull(),
            'status_id' => $this->integer()->unsigned()->notNull(),
            'contact_id' => $this->integer()->unsigned()->null(),
            'department_id' => $this->integer()->unsigned()->null(),
            'subject' => $this->text()->null(),
            'content' => $this->text()->null(),
            'email' => $this->text()->notNull(),
            'name' => $this->text()->notNull(),
            'carbon_copy' => $this->text()->null(),
            'blind_carbon_copy' => $this->text()->null(),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%ticket_attachment}}', [
            'id' => $this->primaryKey()->unsigned(),
            'ticket_id' => $this->integer()->unsigned()->notNull(),
            'file' => $this->text()->notNull(),
            'uploaded_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%ticket_reply}}', [
            'id' => $this->primaryKey()->unsigned(),
            'ticket_id' => $this->integer()->unsigned()->notNull(),
            'staff_id' => $this->integer()->unsigned()->null(),
            'contact_id' => $this->integer()->unsigned()->null(),
            'email' => $this->text()->notNull(),
            'carbon_copy' => $this->text()->null(),
            'blind_carbon_copy' => $this->text()->null(),
            'name' => $this->text()->null(),
            'content' => $this->text()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%ticket_reply_attachment}}', [
            'id' => $this->primaryKey()->unsigned(),
            'reply_id' => $this->integer()->unsigned()->notNull(),
            'file' => $this->text()->notNull(),
            'uploaded_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%ticket_predefined_reply}}', [
            'id' => $this->primaryKey()->unsigned(),
            'title' => $this->text()->notNull(),
            'content' => $this->text()->notNull(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->addForeignKey(
            'creator_of_ticket_department',
            '{{%ticket_department}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_ticket_department',
            '{{%ticket_department}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'creator_of_ticket_status',
            '{{%ticket_status}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_ticket_status',
            '{{%ticket_status}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'creator_of_ticket_priority',
            '{{%ticket_priority}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_ticket_priority',
            '{{%ticket_priority}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'creator_of_ticket_predefined_reply',
            '{{%ticket_predefined_reply}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_ticket_predefined_reply',
            '{{%ticket_predefined_reply}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'status_of_ticket',
            '{{%ticket}}', 'status_id',
            '{{%ticket_status}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'priority_of_ticket',
            '{{%ticket}}', 'priority_id',
            '{{%ticket_priority}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'contact_of_ticket',
            '{{%ticket}}', 'contact_id',
            '{{%customer_contact}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'department_of_ticket',
            '{{%ticket}}', 'department_id',
            '{{%ticket_department}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'creator_of_ticket',
            '{{%ticket}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_ticket',
            '{{%ticket}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'ticket_of_attachment',
            '{{%ticket_attachment}}', 'ticket_id',
            '{{%ticket}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'ticket_of_reply',
            '{{%ticket_reply}}', 'ticket_id',
            '{{%ticket}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'staff_of_reply',
            '{{%ticket_reply}}', 'staff_id',
            '{{%staff}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'contact_of_reply',
            '{{%ticket_reply}}', 'contact_id',
            '{{%customer_contact}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'reply_of_attachment',
            '{{%ticket_reply_attachment}}', 'reply_id',
            '{{%ticket_reply}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->registerDefaults();

        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        $time = time();
        $this->beginCommand('Register permissions');

        if (!$auth->installPermissions($this->permissions())) {
            return false;
        }

        $this->endCommand($time);
    }

    public function permissions()
    {
        return [
            'admin.ticket' => [
                'parent' => 'admin.root',
                'description' => 'Manage Ticket',
            ],
            'admin.ticket.list' => [
                'parent' => 'admin.ticket',
                'description' => 'List of Ticket',
            ],
            'admin.ticket.add' => [
                'parent' => 'admin.ticket',
                'description' => 'Add Ticket',
            ],
            'admin.ticket.update' => [
                'parent' => 'admin.ticket',
                'description' => 'Update Ticket',
            ],
            'admin.ticket.status' => [
                'parent' => 'admin.lead',
                'description' => 'Update Ticket Status',
            ],
            'admin.ticket.priority' => [
                'parent' => 'admin.lead',
                'description' => 'Update Ticket Priority',
            ],
            'admin.ticket.view' => [
                'parent' => 'admin.ticket',
                'description' => 'View Ticket',
            ],
            'admin.ticket.view.detail' => [
                'parent' => 'admin.ticket.view',
                'description' => 'Ticket Details',
            ],
            'admin.ticket.view.task' => [
                'parent' => 'admin.ticket.view',
                'description' => 'Ticket Task',
            ],
            'admin.ticket.view.history' => [
                'parent' => 'admin.ticket.view',
                'description' => 'Ticket History',
            ],
            'admin.ticket.delete' => [
                'parent' => 'admin.ticket',
                'description' => 'Delete Ticket',
            ],

            'admin.setting.ticket' => [
                'parent' => 'admin.setting',
                'description' => 'Ticket Setting',
            ],
            'admin.setting.ticket.general' => [
                'parent' => 'admin.setting.ticket',
                'description' => 'Ticket General Setting',
            ],

            'admin.setting.ticket.status' => [
                'parent' => 'admin.setting.ticket',
                'description' => 'Ticket Status',
            ],
            'admin.setting.ticket.status.list' => [
                'parent' => 'admin.setting.ticket.status',
                'description' => 'List of Ticket Status',
            ],
            'admin.setting.ticket.status.add' => [
                'parent' => 'admin.setting.ticket.status',
                'description' => 'Add Ticket Status',
            ],
            'admin.setting.ticket.status.update' => [
                'parent' => 'admin.setting.ticket.status',
                'description' => 'Update Ticket Status',
            ],
            'admin.setting.ticket.status.delete' => [
                'parent' => 'admin.setting.ticket.status',
                'description' => 'Delete Ticket Status',
            ],
            'admin.setting.ticket.status.visibility' => [
                'parent' => 'admin.setting.ticket.status',
                'description' => 'Enable/Disable Ticket Status',
            ],

            'admin.setting.ticket.priority' => [
                'parent' => 'admin.setting.ticket',
                'description' => 'Ticket Priority',
            ],
            'admin.setting.ticket.priority.list' => [
                'parent' => 'admin.setting.ticket.priority',
                'description' => 'List of Ticket Priority',
            ],
            'admin.setting.ticket.priority.add' => [
                'parent' => 'admin.setting.ticket.priority',
                'description' => 'Add Ticket Priority',
            ],
            'admin.setting.ticket.priority.update' => [
                'parent' => 'admin.setting.ticket.priority',
                'description' => 'Update Ticket Priority',
            ],
            'admin.setting.ticket.priority.delete' => [
                'parent' => 'admin.setting.ticket.priority',
                'description' => 'Delete Ticket Priority',
            ],
            'admin.setting.ticket.priority.visibility' => [
                'parent' => 'admin.setting.ticket.priority',
                'description' => 'Enable/Disable Ticket Priority',
            ],

            'admin.setting.ticket.department' => [
                'parent' => 'admin.setting.ticket',
                'description' => 'Ticket Department',
            ],
            'admin.setting.ticket.department.list' => [
                'parent' => 'admin.setting.ticket.department',
                'description' => 'List of Ticket Department',
            ],
            'admin.setting.ticket.department.add' => [
                'parent' => 'admin.setting.ticket.department',
                'description' => 'Add Ticket Department',
            ],
            'admin.setting.ticket.department.update' => [
                'parent' => 'admin.setting.ticket.department',
                'description' => 'Update Ticket Department',
            ],
            'admin.setting.ticket.department.delete' => [
                'parent' => 'admin.setting.ticket.department',
                'description' => 'Delete Ticket Department',
            ],
            'admin.setting.ticket.department.visibility' => [
                'parent' => 'admin.setting.ticket.department',
                'description' => 'Enable/Disable Ticket Department',
            ],

            'admin.setting.ticket.predefined-reply' => [
                'parent' => 'admin.setting.ticket',
                'description' => 'Ticket Predefined Reply',
            ],
            'admin.setting.ticket.predefined-reply.list' => [
                'parent' => 'admin.setting.ticket.predefined-reply',
                'description' => 'List of Ticket Predefined Reply',
            ],
            'admin.setting.ticket.predefined-reply.add' => [
                'parent' => 'admin.setting.ticket.predefined-reply',
                'description' => 'Add Ticket Predefined Reply',
            ],
            'admin.setting.ticket.predefined-reply.update' => [
                'parent' => 'admin.setting.ticket.predefined-reply',
                'description' => 'Update Ticket Predefined Reply',
            ],
            'admin.setting.ticket.predefined-reply.delete' => [
                'parent' => 'admin.setting.ticket.predefined-reply',
                'description' => 'Delete Ticket Predefined Reply',
            ],
            'admin.setting.ticket.predefined-reply.visibility' => [
                'parent' => 'admin.setting.ticket.predefined-reply',
                'description' => 'Enable/Disable Ticket Predefined Reply',
            ],
        ];
    }

    protected function registerDefaults()
    {
        /** @var Setting $setting */
        $setting = Yii::$app->setting;

        $statuses = [
            [
                'label' => Yii::t('app', 'Not Started'),
                'color_label' => "#444444",
            ],
            [
                'label' => Yii::t('app', 'In Progress'),
                'color_label' => "#ff9800",
            ],
            [
                'label' => Yii::t('app', 'Feedback'),
                'color_label' => "#e83e8c",
            ],
            [
                'label' => Yii::t('app', 'Completed'),
                'color_label' => "#28a745",
            ],
            [
                'label' => Yii::t('app', 'Closed'),
                'color_label' => "#468bef",
            ],
        ];

        $priorities = [
            [
                'label' => Yii::t('app', 'Low'),
                'color_label' => "#444444",
            ],
            [
                'label' => Yii::t('app', 'Medium'),
                'color_label' => "#ff9800",
            ],
            [
                'label' => Yii::t('app', 'High'),
                'color_label' => "#dc3545",
            ],
            [
                'label' => Yii::t('app', 'Urgent'),
                'color_label' => "#ac1dc6",
            ],
        ];

        $departments = [
            [
                'name' => Yii::t('app', 'Billing'),
            ],
            [
                'name' => Yii::t('app', 'Sales'),
            ],
            [
                'name' => Yii::t('app', 'Replacement'),
            ],
            [
                'name' => Yii::t('app', 'Shipping & Delivery'),
            ],
        ];

        foreach ($statuses AS $index => $status) {
            $time = $this->beginCommand("Add status \"{$status['label']}\"");

            $model = new TicketStatus($status);
            $model->scenario = 'install';
            $model->is_enabled = true;
            $model->order = $index;

            if (!$model->save(false)) {
                return false;
            }

            $this->endCommand($time);
        }

        foreach ($priorities AS $index => $priority) {
            $time = $this->beginCommand("Add priority \"{$priority['label']}\"");

            $model = new TicketPriority($priority);
            $model->scenario = 'install';
            $model->is_enabled = true;
            $model->order = $index;

            if (!$model->save(false)) {
                return false;
            }

            $this->endCommand($time);
        }

        foreach ($departments AS $index => $department) {
            $time = $this->beginCommand("Add department \"{$department['name']}\"");

            $model = new TicketDepartment($department);
            $model->scenario = 'install';
            $model->is_enabled = true;

            if (!$model->save(false)) {
                return false;
            }

            $this->endCommand($time);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('creator_of_ticket_department', '{{%ticket_department}}');
        $this->dropForeignKey('updater_of_ticket_department', '{{%ticket_department}}');

        $this->dropForeignKey('creator_of_ticket_priority', '{{%ticket_priority}}');
        $this->dropForeignKey('updater_of_ticket_priority', '{{%ticket_priority}}');

        $this->dropForeignKey('creator_of_ticket_status', '{{%ticket_status}}');
        $this->dropForeignKey('updater_of_ticket_status', '{{%ticket_status}}');

        $this->dropForeignKey('creator_of_ticket_predefined_reply', '{{%ticket_predefined_reply}}');
        $this->dropForeignKey('updater_of_ticket_predefined_reply', '{{%ticket_predefined_reply}}');

        $this->dropForeignKey('status_of_ticket', '{{%ticket}}');
        $this->dropForeignKey('priority_of_ticket', '{{%ticket}}');
        $this->dropForeignKey('contact_of_ticket', '{{%ticket}}');
        $this->dropForeignKey('department_of_ticket', '{{%ticket}}');
        $this->dropForeignKey('creator_of_ticket', '{{%ticket}}');
        $this->dropForeignKey('updater_of_ticket', '{{%ticket}}');

        $this->dropForeignKey('ticket_of_reply', '{{%ticket_reply}}');
        $this->dropForeignKey('staff_of_reply', '{{%ticket_reply}}');
        $this->dropForeignKey('contact_of_reply', '{{%ticket_reply}}');

        $this->dropForeignKey('reply_of_attachment', '{{%ticket_reply_attachment}}');
        $this->dropForeignKey('ticket_of_attachment', '{{%ticket_attachment}}');

        $this->dropTable('{{%ticket_status}}');
        $this->dropTable('{{%ticket_priority}}');
        $this->dropTable('{{%ticket_department}}');
        $this->dropTable('{{%ticket}}');
        $this->dropTable('{{%ticket_attachment}}');
        $this->dropTable('{{%ticket_reply}}');
        $this->dropTable('{{%ticket_reply_attachment}}');
        $this->dropTable('{{%ticket_predefined_reply}}');

        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        if (!$auth->uninstallPermissions($this->permissions())) {
            return false;
        }

        return true;
    }
}
