<?php

namespace modules\note\migrations;

use yii\db\Migration;

/**
 * Class M190603030202Note
 */
class M190603030202Note extends Migration
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

        $this->createTable('{{%note}}', [
            'id' => $this->primaryKey()->unsigned(),
            'creator_id' => $this->integer()->unsigned()->notNull(),
            'model' => $this->text()->null(),
            'model_id' => $this->text()->null(),
            'color' => $this->char(7),
            'title' => $this->text()->null(),
            'content' => $this->text()->null(),
            'is_private' => $this->boolean()->defaultValue(0),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable("{{%note_attachment}}", [
            'id' => $this->primaryKey()->unsigned(),
            'note_id' => $this->integer()->unsigned()->notNull(),
            'file' => $this->text()->notNull(),
            'uploaded_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->addForeignKey(
            'attachment_of_note',
            '{{%note_attachment}}', 'note_id',
            '{{%note}}', 'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('attachment_of_note', '{{%note_attachment}}');

        $this->dropTable('{{%note}}');
        $this->dropTable('{{%note_attachment}}');
    }

}
