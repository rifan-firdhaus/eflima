<?php

namespace modules\account\migrations;

use yii\db\Migration;

/**
 * Class M190616152740Comment
 */
class M190617152740Comment extends Migration
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

        $this->createTable('{{%account_comment}}', [
            'id' => $this->primaryKey()->unsigned(),
            'model' => $this->text()->null(),
            'model_id' => $this->text()->null(),
            'account_id' => $this->integer()->unsigned()->notNull(),
            'parent_id' => $this->integer()->unsigned()->null(),
            'comment' => $this->text()->null(),
            'posted_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%account_comment_attachment}}', [
            'id' => $this->primaryKey()->unsigned(),
            'comment_id' => $this->integer()->unsigned()->notNull(),
            'file' => $this->text()->notNull(),
            'uploaded_at' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'account_of_comment',
            '{{%account_comment}}', 'account_id',
            '{{%account}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'comment_of_attachment',
            '{{%account_comment_attachment}}', 'comment_id',
            '{{%account_comment}}', 'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('account_of_comment', '{{%account_comment}}');
        $this->dropForeignKey('comment_of_attachment', '{{%account_comment_attachment}}');

        $this->dropTable('{{%account_comment}}');
        $this->dropTable('{{%account_comment_attachment}}');
    }
}
