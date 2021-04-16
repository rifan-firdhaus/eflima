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
            'model' => $this->text()->null(),
            'model_id' => $this->text()->null(),
            'color' => $this->char(7),
            'title' => $this->text()->null(),
            'content' => $this->text()->null(),
            'is_pinned' => $this->boolean()->defaultValue(0),
            'is_private' => $this->boolean()->defaultValue(0),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
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

        $this->addForeignKey(
            'creator_of_note',
            '{{%note}}', 'creator_id',
            '{{%account}}', 'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'updater_of_note',
            '{{%note}}', 'updater_id',
            '{{%account}}', 'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('creator_of_note', '{{%note}}');
        $this->dropForeignKey('updater_of_note', '{{%note}}');

        $this->dropForeignKey('attachment_of_note', '{{%note_attachment}}');

        $this->dropTable('{{%note}}');
        $this->dropTable('{{%note_attachment}}');
    }

}
