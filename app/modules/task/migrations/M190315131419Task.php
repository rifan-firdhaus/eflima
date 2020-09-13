<?php namespace modules\task\migrations;

use modules\core\components\Setting;
use modules\core\db\MigrationSettingInstaller;
use modules\task\models\TaskPriority;
use modules\task\models\TaskStatus;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Migration;

/**
 * Class M190315131419Task
 */
class M190315131419Task extends Migration
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

        $this->createTable('{{%task}}', [
            'id' => $this->primaryKey()->unsigned(),
            'parent_id' => $this->integer()->unsigned()->null(),
            'model' => $this->text()->null(),
            'model_id' => $this->text()->null(),
            'status_id' => $this->integer()->null()->unsigned(),
            'priority_id' => $this->integer()->null()->unsigned(),
            'title' => $this->text()->notNull(),
            'description' => $this->text()->null(),
            'started_date' => $this->integer()->unsigned()->null(),
            'deadline_date' => $this->integer()->unsigned()->null(),
            'progress' => $this->decimal(5, 4)->defaultValue(0),
            'estimation' => $this->decimal(7, 2)->null(),
            'estimation_modifier' => $this->char(1)->null(),
            'is_timer_enabled' => $this->boolean()->unsigned()->defaultValue(1),
            'timer_type' => $this->char(1)->null(),
            'is_timer_active' => $this->boolean()->unsigned()->defaultValue(0),
            'is_individual_timer' => $this->boolean()->unsigned()->defaultValue(0),
            'is_billable' => $this->boolean()->unsigned()->defaultValue(0),
            'is_archieved' => $this->boolean()->unsigned()->defaultValue(0),
            'price' => $this->decimal(25, 8)->defaultValue(0),
            'price_modifier' => $this->char(1)->null(),
            'progress_calculation' => $this->char(1)->notNull(),
            'visibility' => $this->char(1)->notNull(),
            'is_comment_allowed' => $this->boolean()->unsigned()->defaultValue(1),
            'is_customer_allowed_to_comment' => $this->boolean()->unsigned()->defaultValue(0),
            'is_notified_when_comment' => $this->boolean()->unsigned()->defaultValue(1),
            'is_notified_only_when_customer_comment' => $this->boolean()->unsigned()->defaultValue(1),
            'is_notified_when_progress_updated' => $this->boolean()->unsigned()->defaultValue(1),
            'is_checklist_exists' => $this->boolean()->unsigned()->defaultValue(0),
            'creator_id' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%task_timer}}', [
            'id' => $this->primaryKey()->unsigned(),
            'task_id' => $this->integer()->unsigned()->notNull(),
            'starter_id' => $this->integer()->unsigned()->notNull(),
            'stopper_id' => $this->integer()->unsigned()->null(),
            'started_at' => $this->integer()->unsigned()->notNull(),
            'stopped_at' => $this->integer()->unsigned()->null(),
            'is_approved' => $this->boolean()->unsigned()->defaultValue(0),
            'approver_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%task_assignee}}', [
            'id' => $this->primaryKey()->unsigned(),
            'task_id' => $this->integer()->unsigned()->notNull(),
            'assignee_id' => $this->integer()->unsigned()->notNull(),
            'assigned_at' => $this->integer()->unsigned()->notNull(),
            'assignor_id' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);

        $this->createTable('{{%task_follower}}', [
            'id' => $this->primaryKey()->unsigned(),
            'task_id' => $this->integer()->unsigned()->notNull(),
            'follower_id' => $this->integer()->unsigned()->notNull(),
            'is_notified_when_timer_start' => $this->boolean()->defaultValue(1)->unsigned(),
            'is_notified_when_timer_end' => $this->boolean()->defaultValue(1)->unsigned(),
            'is_notified_when_comment' => $this->boolean()->defaultValue(1)->unsigned(),
            'is_notified_only_when_customer_comment' => $this->boolean()->defaultValue(1)->unsigned(),
            'is_notified_only_when_progress_updated' => $this->boolean()->defaultValue(1)->unsigned(),
            'followed_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%task_checklist}}', [
            'id' => $this->primaryKey()->unsigned(),
            'task_id' => $this->integer()->unsigned()->notNull(),
            'label' => $this->text()->notNull(),
            'is_checked' => $this->boolean()->defaultValue(0)->unsigned(),
            'order' => $this->integer(3)->unsigned()->null(),
            'checked_at' => $this->integer()->null()->unsigned(),
            'checker_id' => $this->integer()->null()->unsigned(),
            'created_at' => $this->integer()->null()->unsigned(),
            'updated_at' => $this->integer()->null()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%task_status}}', [
            'id' => $this->primaryKey()->unsigned(),
            'label' => $this->text()->notNull(),
            'color_label' => $this->char(7)->null(),
            'order' => $this->integer(3)->unsigned()->defaultValue(100),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'description' => $this->text()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%task_priority}}', [
            'id' => $this->primaryKey()->unsigned(),
            'label' => $this->text()->notNull(),
            'color_label' => $this->char(7)->null(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'description' => $this->text()->null(),
            'order' => $this->integer(3)->unsigned()->defaultValue(100),
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%task_interaction}}', [
            'id' => $this->primaryKey()->unsigned(),
            'task_id' => $this->integer()->unsigned()->notNull(),
            'staff_id' => $this->integer()->unsigned()->notNull(),
            'type' => $this->char(1)->null(),
            'status_id' => $this->integer()->unsigned()->null(),
            'progress' => $this->decimal(5, 4)->null(),
            'comment' => $this->text()->null(),
            'at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable("{{%task_attachment}}", [
            'id' => $this->primaryKey()->unsigned(),
            'task_id' => $this->integer()->unsigned()->notNull(),
            'file' => $this->text()->notNull(),
            'uploaded_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable("{{%task_interaction_attachment}}", [
            'id' => $this->primaryKey()->unsigned(),
            'interaction_id' => $this->integer()->unsigned()->notNull(),
            'file' => $this->text()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'status_of_task',
            '{{%task}}', 'status_id',
            '{{%task_status}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'parent_of_task',
            '{{%task}}', 'parent_id',
            '{{%task}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'priority_of_task',
            '{{%task}}', 'priority_id',
            '{{%task_priority}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'creator_of_task',
            '{{%task}}', 'creator_id',
            '{{%staff}}', 'id',
            'RESTRICT',
            'CASCADE'
        );


        $this->addForeignKey(
            'task_of_assignee',
            '{{%task_assignee}}', 'task_id',
            '{{%task}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'profile_of_assignee',
            '{{%task_assignee}}', 'assignee_id',
            '{{%staff}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'profile_of_assignor',
            '{{%task_assignee}}', 'assignor_id',
            '{{%staff}}', 'id',
            'RESTRICT',
            'CASCADE'
        );


        $this->addForeignKey(
            'task_of_follower',
            '{{%task_follower}}', 'task_id',
            '{{%task}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'profile_of_follower',
            '{{%task_follower}}', 'follower_id',
            '{{%staff}}', 'id',
            'RESTRICT',
            'CASCADE'
        );


        $this->addForeignKey(
            'task_of_timer',
            '{{%task_timer}}', 'task_id',
            '{{%task}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'starter_of_timer',
            '{{%task_timer}}', 'starter_id',
            '{{%staff}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'stopper_of_timer',
            '{{%task_timer}}', 'stopper_id',
            '{{%staff}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'approver_of_timer',
            '{{%task_timer}}', 'approver_id',
            '{{%staff}}', 'id',
            'RESTRICT',
            'CASCADE'
        );


        $this->addForeignKey(
            'task_of_checklist',
            '{{%task_checklist}}', 'task_id',
            '{{%task}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'checker_of_checklist',
            '{{%task_checklist}}', 'checker_id',
            '{{%staff}}', 'id',
            'RESTRICT',
            'CASCADE'
        );


        $this->addForeignKey(
            'task_of_interaction',
            '{{%task_interaction}}', 'task_id',
            '{{%task}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'staff_of_interaction',
            '{{%task_interaction}}', 'staff_id',
            '{{%staff}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'status_of_interaction',
            '{{%task_interaction}}', 'status_id',
            '{{%task_status}}', 'id',
            'RESTRICT',
            'CASCADE'
        );


        $this->addForeignKey(
            'attachment_of_task',
            '{{%task_attachment}}', 'task_id',
            '{{%task}}', 'id',
            'CASCADE',
            'CASCADE'
        );


        $this->addForeignKey(
            'interaction_of_attachment',
            '{{%task_interaction_attachment}}', 'interaction_id',
            '{{%task_interaction}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        return $this->registerSettings() && $this->registerDefaults();
    }

    /**
     * @return array
     */
    protected function settings()
    {
        return [
            [
                'id' => 'task/default_status',
            ],
            [
                'id' => 'task/completed_status',
            ],
            [
                'id' => 'task/closed_status',
            ],
            [
                'id' => 'task/default_priority',
            ],
            [
                'id' => 'task/is_subtask_allowed',
                'value' => 1,
            ],
            [
                'id' => 'task/is_checklist_allowed',
                'value' => 1,
            ],
            [
                'id' => 'task/notify_before_deadline_period',
            ],
        ];
    }

    /**
     * @return bool
     *
     * @throws InvalidConfigException
     */
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

        foreach ($statuses AS $index => $status) {
            $time = $this->beginCommand("Add status \"{$status['label']}\"");

            $model = new TaskStatus($status);
            $model->scenario = 'install';
            $model->is_enabled = true;
            $model->order = $index;

            if (!$model->save(false)) {
                return false;
            }

            switch ($index) {
                case 0:
                    $setting->set('task/default_status', $model->id);
                    break;
                case 3:
                    $setting->set('task/completed_status', $model->id);
                    break;
                case 4:
                    $setting->set('task/closed_status', $model->id);
                    break;
            }

            $this->endCommand($time);
        }

        foreach ($priorities AS $index => $priority) {
            $time = $this->beginCommand("Add priority \"{$priority['label']}\"");

            $model = new TaskPriority($priority);
            $model->scenario = 'install';
            $model->is_enabled = true;
            $model->order = $index;

            if (!$model->save(false)) {
                return false;
            }

            if ($index === 0) {
                $setting->set('task/default_priority', $model->id);
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
        $this->dropForeignKey('status_of_task', '{{%task}}');
        $this->dropForeignKey('parent_of_task', '{{%task}}');
        $this->dropForeignKey('priority_of_task', '{{%task}}');
        $this->dropForeignKey('creator_of_task', '{{%task}}');

        $this->dropForeignKey('task_of_assignee', '{{%task_assignee}}');
        $this->dropForeignKey('profile_of_assignee', '{{%task_assignee}}');
        $this->dropForeignKey('profile_of_assignor', '{{%task_assignee}}');

        $this->dropForeignKey('task_of_follower', '{{%task_follower}}');
        $this->dropForeignKey('profile_of_follower', '{{%task_follower}}');

        $this->dropForeignKey('task_of_timer', '{{%task_timer}}');
        $this->dropForeignKey('starter_of_timer', '{{%task_timer}}');
        $this->dropForeignKey('stopper_of_timer', '{{%task_timer}}');
        $this->dropForeignKey('approver_of_timer', '{{%task_timer}}');

        $this->dropForeignKey('task_of_checklist', '{{%task_checklist}}');
        $this->dropForeignKey('checker_of_checklist', '{{%task_checklist}}');

        $this->dropForeignKey('task_of_interaction', '{{%task_interaction}}');
        $this->dropForeignKey('staff_of_interaction', '{{%task_interaction}}');
        $this->dropForeignKey('status_of_interaction', '{{%task_interaction}}');

        $this->dropForeignKey('interaction_of_attachment', '{{%task_interaction_attachment}}');

        $this->dropForeignKey('attachment_of_task', '{{%task_attachment}}');

        $this->dropTable('{{%task}}');
        $this->dropTable('{{%task_priority}}');
        $this->dropTable('{{%task_status}}');
        $this->dropTable('{{%task_follower}}');
        $this->dropTable('{{%task_assignee}}');
        $this->dropTable('{{%task_timer}}');
        $this->dropTable('{{%task_checklist}}');
        $this->dropTable('{{%task_interaction}}');
        $this->dropTable('{{%task_interaction_attachment}}');
        $this->dropTable('{{%task_attachment}}');

        return $this->unregisterSettings();
    }
}
