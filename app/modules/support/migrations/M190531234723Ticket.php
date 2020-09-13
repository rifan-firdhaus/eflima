<?php

namespace modules\support\migrations;

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
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%ticket_department}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->text()->notNull(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'description' => $this->text()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%ticket_priority}}', [
            'id' => $this->primaryKey()->unsigned(),
            'label' => $this->text()->notNull(),
            'color_label' => $this->char(7)->null(),
            'order' => $this->integer(3)->unsigned()->defaultValue(100),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'description' => $this->text()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
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
            'created_at' => $this->integer()->unsigned()->null(),
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
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

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
        $this->dropForeignKey('status_of_ticket', '{{%ticket}}');
        $this->dropForeignKey('priority_of_ticket', '{{%ticket}}');
        $this->dropForeignKey('contact_of_ticket', '{{%ticket}}');
        $this->dropForeignKey('department_of_ticket', '{{%ticket}}');

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

        return true;
    }
}
