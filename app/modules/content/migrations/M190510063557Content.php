<?php

namespace modules\content\migrations;

use yii\db\Migration;

/**
 * Class M190510063557Content
 */
class M190510063557Content extends Migration
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

        $this->createTable('{{%post}}', [
            'id' => $this->primaryKey()->unsigned(),
            'title' => $this->text()->notNull(),
            'slug' => $this->text()->notNull(),
            'content' => $this->text()->notNull(),
            'type_id' => $this->string(36)->notNull(),
            'is_published' => $this->boolean()->defaultValue(0),
            'picture' => $this->text()->null(),
            'published_at' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%post_taxonomy}}', [
            'id' => $this->primaryKey()->unsigned(),
            'parent_id' => $this->integer()->unsigned(),
            'title' => $this->text()->notNull(),
            'picture' => $this->text()->null(),
            'type_id' => $this->string(36)->notNull(),
            'content' => $this->text()->null(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%post_taxonomy_relationship}}', [
            'id' => $this->primaryKey()->unsigned(),
            'post_id' => $this->integer()->unsigned()->notNull(),
            'taxonomy_id' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'parent_of_taxonomy',
            '{{%post_taxonomy}}', 'parent_id',
            '{{%post_taxonomy}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'post_of_taxonomy',
            '{{%post_taxonomy_relationship}}', 'post_id',
            '{{%post}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'taxonomy_of_post',
            '{{%post_taxonomy_relationship}}', 'taxonomy_id',
            '{{%post_taxonomy}}', 'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('parent_of_taxonomy', '{{%post_taxonomy}}');
        $this->dropForeignKey('post_of_taxonomy', '{{%post_taxonomy_relationship}}');
        $this->dropForeignKey('taxonomy_of_post', '{{%post_taxonomy_relationship}}');

        $this->dropTable('{{%post}}');
        $this->dropTable('{{%post_taxonomy}}');
        $this->dropTable('{{%post_taxonomy_relationship}}');

        return true;
    }
}
