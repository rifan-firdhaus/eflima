<?php

namespace modules\project\migrations;

use modules\account\rbac\DbManager;
use modules\core\components\Setting;
use modules\project\models\ProjectStatus;
use Yii;
use yii\db\Migration;

/**
 * Class M190531234724Project
 */
class M190531234724Project extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%project_status}}', [
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

        $this->createTable('{{%project}}', [
            'id' => $this->primaryKey()->unsigned(),
            'customer_id' => $this->integer()->unsigned()->notNull(),
            'status_id' => $this->integer()->unsigned()->notNull(),
            'currency_code' => $this->char(3)->notNull()->null(),
            'name' => $this->text()->notNull(),
            'description' => $this->text()->null(),
            'progress' => $this->decimal(5, 4)->defaultValue(0),
            'is_progress_calcuted_through_task' => $this->boolean()->defaultValue(0),
            'budget' => $this->decimal()->defaultValue(0)->null(),
            'started_date' => $this->integer()->unsigned()->notNull(),
            'deadline_date' => $this->integer()->unsigned()->null(),
            'visibility' => $this->char(1)->notNull(),
            'is_visible_to_customer' => $this->boolean()->defaultValue(1),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%project_attachment}}', [
            'id' => $this->primaryKey()->unsigned(),
            'project_id' => $this->integer()->unsigned()->notNull(),
            'file' => $this->text()->notNull(),
        ], $tableOptions);

        $this->createTable('{{%project_milestone}}', [
            'id' => $this->primaryKey()->unsigned(),
            'project_id' => $this->integer()->unsigned()->notNull(),
            'name' => $this->text()->null(),
            'color' => $this->text()->null(),
            'description' => $this->text()->null(),
            'started_date' => $this->integer()->unsigned()->null(),
            'deadline_date' => $this->integer()->unsigned()->null(),
            'order' => $this->integer()->unsigned()->defaultValue(99),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%project_member}}', [
            'id' => $this->primaryKey()->unsigned(),
            'project_id' => $this->integer()->unsigned()->notNull(),
            'staff_id' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);

        $this->createTable('{{%project_discussion_topic}}', [
            'id' => $this->primaryKey()->unsigned(),
            'project_id' => $this->integer()->unsigned()->notNull(),
            'subject' => $this->text()->notNull(),
            'content' => $this->text()->null(),
            'is_internal' => $this->boolean()->defaultValue(0),
            'is_closed' => $this->boolean()->defaultValue(0),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->addColumn('{{%task}}', 'milestone_id', $this->integer()->unsigned()->null());
        $this->addColumn('{{%task}}', 'milestone_order', $this->integer()->unsigned()->null());
        $this->addColumn('{{%invoice}}', 'project_id', $this->integer()->unsigned()->null());
        $this->addColumn('{{%expense}}', 'project_id', $this->integer()->unsigned()->null());
        $this->addColumn('{{%ticket}}', 'project_id', $this->integer()->unsigned()->null());

        $this->addForeignKey(
            'creator_of_project_status',
            '{{%project_status}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_project_status',
            '{{%project_status}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'project_of_invoice',
            '{{%invoice}}', 'project_id',
            '{{%project}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'project_of_expense',
            '{{%expense}}', 'project_id',
            '{{%project}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'milestone_of_task',
            '{{%task}}', 'milestone_id',
            '{{%project_milestone}}', 'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'project_of_milestone',
            '{{%project_milestone}}', 'project_id',
            '{{%project}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'creator_of_project_milestone',
            '{{%project_milestone}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_project_milestone',
            '{{%project_milestone}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'project_of_ticket',
            '{{%ticket}}', 'project_id',
            '{{%project}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'customer_of_project',
            '{{%project}}', 'customer_id',
            '{{%customer}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'status_of_project',
            '{{%project}}', 'status_id',
            '{{%project_status}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'creator_of_project',
            '{{%project}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_project',
            '{{%project}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'project_of_attachment',
            '{{%project_attachment}}', 'project_id',
            '{{%project}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'project_of_member',
            '{{%project_member}}', 'project_id',
            '{{%project}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'staff_of_project_member',
            '{{%project_member}}', 'staff_id',
            '{{%staff}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'project_of_discussion_topic',
            '{{%project_discussion_topic}}', 'project_id',
            '{{%project}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'creator_of_project_discussion_topic',
            '{{%project_discussion_topic}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_project_discussion_topic',
            '{{%project_discussion_topic}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
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

    public function registerDefaults()
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
                'color_label' => "#ccb616",
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

        foreach ($statuses AS $index => $status) {
            $time = $this->beginCommand("Add status \"{$status['label']}\"");

            $model = new ProjectStatus($status);
            $model->scenario = 'install';
            $model->is_enabled = true;
            $model->order = $index;

            if (!$model->save(false)) {
                return false;
            }

            $this->endCommand($time);
        }
    }

    /**
     * @return array
     */
    public function permissions()
    {
        return [
            'admin.project' => [
                'parent' => 'admin.root',
                'description' => 'Manage Project',
            ],
            'admin.project.list' => [
                'parent' => 'admin.project',
                'description' => 'List of Project',
            ],
            'admin.project.add' => [
                'parent' => 'admin.project',
                'description' => 'Add Project',
            ],
            'admin.project.update' => [
                'parent' => 'admin.project',
                'description' => 'Update Project',
            ],
            'admin.project.status' => [
                'parent' => 'admin.project',
                'description' => 'Update Project Status',
            ],
            'admin.project.member' => [
                'parent' => 'admin.project',
                'description' => 'Invite Project Member',
            ],
            'admin.project.view' => [
                'parent' => 'admin.project',
                'description' => 'View Project Details',
            ],
            'admin.project.view.detail' => [
                'parent' => 'admin.project.view',
                'description' => 'Project Detail',
            ],
            'admin.project.view.task' => [
                'parent' => 'admin.project.view',
                'description' => 'Project Task',
            ],
            'admin.project.view.task-timer' => [
                'parent' => 'admin.project.view',
                'description' => 'Project Timesheet',
            ],
            'admin.project.view.ticket' => [
                'parent' => 'admin.project.view',
                'description' => 'Project Ticket',
            ],
            'admin.project.view.invoice' => [
                'parent' => 'admin.project.view',
                'description' => 'Project Invoice',
            ],
            'admin.project.view.expense' => [
                'parent' => 'admin.project.view',
                'description' => 'Project Expense',
            ],
            'admin.project.view.payment' => [
                'parent' => 'admin.project.view',
                'description' => 'Project Payment',
            ],
            'admin.project.view.discussion' => [
                'parent' => 'admin.project.view',
                'description' => 'Project Discussion',
            ],
            'admin.project.view.discussion.view' => [
                'parent' => 'admin.project.view.discussion',
                'description' => 'View Discussion',
            ],
            'admin.project.view.discussion.add' => [
                'parent' => 'admin.project.view.discussion',
                'description' => 'Add Discussion Topic',
            ],
            'admin.project.view.discussion.update' => [
                'parent' => 'admin.project.view.discussion',
                'description' => 'Update Discussion Topic',
            ],
            'admin.project.view.discussion.delete' => [
                'parent' => 'admin.project.view.discussion',
                'description' => 'Delete Discussion Topic',
            ],
            'admin.project.view.event' => [
                'parent' => 'admin.project.view',
                'description' => 'Project Event',
            ],
            'admin.project.view.milestone' => [
                'parent' => 'admin.project.view',
                'description' => 'Project Milestone',
            ],
            'admin.project.view.milestone.list' => [
                'parent' => 'admin.project.view.milestone',
                'description' => 'List of Milestone',
            ],
            'admin.project.view.milestone.add' => [
                'parent' => 'admin.project.view.milestone',
                'description' => 'Add Milestone',
            ],
            'admin.project.view.milestone.update' => [
                'parent' => 'admin.project.view.milestone',
                'description' => 'Update Milestone',
            ],
            'admin.project.view.milestone.delete' => [
                'parent' => 'admin.project.view.milestone',
                'description' => 'Delete Milestone',
            ],
            'admin.project.view.milestone.task' => [
                'parent' => 'admin.project.view.milestone',
                'description' => 'Add/Move/Remove Task to/from Milestone',
            ],
            'admin.project.view.history' => [
                'parent' => 'admin.project.view',
                'description' => 'Project History',
            ],
            'admin.project.delete' => [
                'parent' => 'admin.project',
                'description' => 'Delete Project',
            ],

            'admin.customer.view.project' => [
                'parent' => 'admin.customer.view',
                'description' => 'Customer Project'
            ],

            'admin.setting.project' => [
                'parent' => 'admin.setting',
                'description' => 'Project Setting',
            ],
            'admin.setting.project.general' => [
                'parent' => 'admin.setting.project',
                'description' => 'Project General Setting',
            ],


            'admin.setting.project.project-status' => [
                'parent' => 'admin.setting.project',
                'description' => 'Project Status',
            ],
            'admin.setting.project.project-status.list' => [
                'parent' => 'admin.setting.project.project-status',
                'description' => 'List of Project Status',
            ],
            'admin.setting.project.project-status.add' => [
                'parent' => 'admin.setting.project.project-status',
                'description' => 'Add Project Status',
            ],
            'admin.setting.project.project-status.update' => [
                'parent' => 'admin.setting.project.project-status',
                'description' => 'Update Project Status',
            ],
            'admin.setting.project.project-status.delete' => [
                'parent' => 'admin.setting.project.project-status',
                'description' => 'Delete Project Status',
            ],
            'admin.setting.project.project-status.visibility' => [
                'parent' => 'admin.setting.project.project-status',
                'description' => 'Enable/Disable Project Status',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('creator_of_project_status', '{{%project_status}}');
        $this->dropForeignKey('updater_of_project_status', '{{%project_status}}');
        $this->dropForeignKey('status_of_project', '{{%project}}');
        $this->dropForeignKey('customer_of_project', '{{%project}}');
        $this->dropForeignKey('creator_of_project', '{{%project}}');
        $this->dropForeignKey('updater_of_project', '{{%project}}');
        $this->dropForeignKey('project_of_attachment', '{{%project_attachment}}');
        $this->dropForeignKey('project_of_invoice', '{{%invoice}}');
        $this->dropForeignKey('project_of_expense', '{{%expense}}');
        $this->dropForeignKey('project_of_ticket', '{{%ticket}}');
        $this->dropForeignKey('milestone_of_task', '{{%task}}');
        $this->dropForeignKey('project_of_member', '{{%project_member}}');
        $this->dropForeignKey('staff_of_project_member', '{{%project_member}}');
        $this->dropForeignKey('project_of_milestone', '{{%project_milestone}}');
        $this->dropForeignKey('creator_of_project_milestone', '{{%project_milestone}}');
        $this->dropForeignKey('updater_of_project_milestone', '{{%project_milestone}}');
        $this->dropForeignKey('project_of_discussion_topic', '{{%project_discussion_topic}}');
        $this->dropForeignKey('creator_of_project_discussion_topic', '{{%project_discussion_topic}}');
        $this->dropForeignKey('updater_of_project_discussion_topic', '{{%project_discussion_topic}}');

        $this->dropColumn('{{%invoice}}', 'project_id');
        $this->dropColumn('{{%expense}}', 'project_id');
        $this->dropColumn('{{%ticket}}', 'project_id');
        $this->dropColumn('{{%task}}', 'milestone_id');
        $this->dropColumn('{{%task}}', 'milestone_order');

        $this->dropTable('{{%project_attachment}}');
        $this->dropTable('{{%project_status}}');
        $this->dropTable('{{%project}}');
        $this->dropTable('{{%project_milestone}}');
        $this->dropTable('{{%project_member}}');
        $this->dropTable('{{%project_discussion_topic}}');

        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        if (!$auth->uninstallPermissions($this->permissions())) {
            return false;
        }

        return true;
    }
}
