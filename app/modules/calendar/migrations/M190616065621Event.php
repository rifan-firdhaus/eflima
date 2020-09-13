<?php

namespace modules\calendar\migrations;

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
            'created_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%event_member}}', [
            'id' => $this->primaryKey()->unsigned(),
            'event_id' => $this->integer()->unsigned()->notNull(),
            'staff_id' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('event_of_member','{{%event_member}}');
        $this->dropForeignKey('staff_of_member','{{%event_member}}');

        $this->dropTable('{{%event}}');
        $this->dropTable('{{%event_member}}');
    }
}
