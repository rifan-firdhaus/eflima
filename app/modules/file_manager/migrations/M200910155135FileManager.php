<?php

namespace modules\file_manager\migrations;

use yii\db\Migration;

/**
 * Class M200910155135FileManager
 */
class M200910155135FileManager extends Migration
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

        $this->createTable('{{%file}}', [
            'id' => $this->primaryKey()->unsigned(),
            'uploader_id' => $this->integer()->unsigned()->notNull(),
            'uuid' => $this->string(255)->notNull(),
            'model' => $this->text()->null(),
            'model_id' => $this->text()->null(),
            'is_public' => $this->boolean()->defaultValue(false),
            'file' => $this->text()->notNull(),
            'uploaded_at' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);

        $this->createTable('{{%file_relationship}}', [
            'id' => $this->primaryKey()->unsigned(),
            'file_id' => $this->integer()->unsigned()->notNull(),
            'model' => $this->text()->null(),
            'model_id' => $this->text()->null(),
            'created_at' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'file_of_relationship',
            '{{%file_relationship}}', 'file_id',
            '{{%file}}', 'id'
        );

        $this->addForeignKey(
            'uploader_of_file',
            '{{%file}}', 'uploader_id',
            '{{%account}}', 'id'
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('uploader_of_file', '{{%file}}');
        $this->dropForeignKey('file_of_relationship', '{{%file_relationship}}');

        $this->dropTable('{{%file}}');
        $this->dropTable('{{%file_relationship}}');

        return true;
    }
}
