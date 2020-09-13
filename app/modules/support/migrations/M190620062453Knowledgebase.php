<?php

namespace modules\support\migrations;

use yii\db\Migration;

/**
 * Class M190620062453Knowledgebase
 */
class M190620062453Knowledgebase extends Migration
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

        $this->createTable('{{%knowledge_base_category}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->text()->null(),
            'description' => $this->text()->null(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%knowledge_base}}', [
            'id' => $this->primaryKey()->unsigned(),
            'category_id' => $this->integer()->unsigned(),
            'title' => $this->text()->null(),
            'content' => $this->text()->notNull(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->addForeignKey(
            'category_of_knowledge_base',
            '{{%knowledge_base}}', 'category_id',
            '{{%knowledge_base_category}}', 'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('category_of_knowledge_base', '{{%knowledge_base}}');

        $this->dropTable('{{%knowledge_base}}');
        $this->dropTable('{{%knowledge_base_category}}');
    }
}
