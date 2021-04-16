<?php

namespace modules\calendar\migrations;

use modules\account\rbac\DbManager;
use Yii;
use yii\db\Migration;

/**
 * Class M190616065621Event
 */
class M190616065621Event extends Migration
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

        $this->createTable('{{%event}}', [
            'id' => $this->primaryKey()->unsigned(),
            'model' => $this->text()->null(),
            'model_id' => $this->text()->null(),
            'name' => $this->text()->notNull(),
            'description' => $this->text()->null(),
            'location' => $this->text()->null(),
            'start_date' => $this->integer()->unsigned()->null(),
            'end_date' => $this->integer()->unsigned()->null(),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%event_member}}', [
            'id' => $this->primaryKey()->unsigned(),
            'event_id' => $this->integer()->unsigned()->notNull(),
            'staff_id' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->addForeignKey(
            'creator_of_event',
            '{{%event}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_event',
            '{{%event}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'event_of_member',
            '{{%event_member}}', 'event_id',
            '{{%event}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'staff_of_member',
            '{{%event_member}}', 'staff_id',
            '{{%staff}}', 'id',
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

    public function permissions()
    {
        return [
            'admin.event' => [
                'parent' => 'admin.root',
                'description' => 'Manage Event'
            ],
            'admin.event.list' => [
                'parent' => 'admin.event',
                'description' => 'List of Event'
            ],
            'admin.event.add' => [
                'parent' => 'admin.event',
                'description' => 'Add Event'
            ],
            'admin.event.update' => [
                'parent' => 'admin.event',
                'description' => 'Update Event'
            ],
            'admin.event.view' => [
                'parent' => 'admin.event',
                'description' => 'View Event Details'
            ],
            'admin.event.delete' => [
                'parent' => 'admin.event',
                'description' => 'Delete Event'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('updater_of_event', '{{%event}}');
        $this->dropForeignKey('creator_of_event', '{{%event}}');

        $this->dropForeignKey('event_of_member', '{{%event_member}}');
        $this->dropForeignKey('staff_of_member', '{{%event_member}}');

        $this->dropTable('{{%event}}');
        $this->dropTable('{{%event_member}}');

        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        if (!$auth->uninstallPermissions($this->permissions())) {
            return false;
        }
    }
}
