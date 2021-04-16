<?php

namespace modules\support\migrations;

use modules\account\rbac\DbManager;
use Yii;
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
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%knowledge_base}}', [
            'id' => $this->primaryKey()->unsigned(),
            'category_id' => $this->integer()->unsigned(),
            'title' => $this->text()->null(),
            'content' => $this->text()->notNull(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'creator_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned(),
            'updater_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->addForeignKey(
            'creator_of_knowledge_base_category',
            '{{%knowledge_base_category}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_knowledge_base_category',
            '{{%knowledge_base_category}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'category_of_knowledge_base',
            '{{%knowledge_base}}', 'category_id',
            '{{%knowledge_base_category}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'creator_of_knowledge_base',
            '{{%knowledge_base}}', 'creator_id',
            '{{%account}}', 'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'updater_of_knowledge_base',
            '{{%knowledge_base}}', 'updater_id',
            '{{%account}}', 'id',
            'NO ACTION'
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

    /**
     * @return array
     */
    public function permissions()
    {
        return [
            'admin.knowledge-base' => [
                'parent' => 'admin.root',
                'description' => 'Manage Knowledge Base',
            ],
            'admin.knowledge-base.list' => [
                'parent' => 'admin.knowledge-base',
                'description' => 'List of Knowledge Base',
            ],
            'admin.knowledge-base.add' => [
                'parent' => 'admin.knowledge-base',
                'description' => 'Add Knowledge Base',
            ],
            'admin.knowledge-base.update' => [
                'parent' => 'admin.knowledge-base',
                'description' => 'Update Knowledge Base',
            ],
            'admin.knowledge-base.view' => [
                'parent' => 'admin.knowledge-base',
                'description' => 'Knowledge Base Detail',
            ],
            'admin.knowledge-base.visibility' => [
                'parent' => 'admin.knowledge-base',
                'description' => 'Enable/Disable Knowledge Base',
            ],
            'admin.knowledge-base.delete' => [
                'parent' => 'admin.knowledge-base',
                'description' => 'Delete Knowledge Base',
            ],

            'admin.knowledge-base.category' => [
                'parent' => 'admin.knowledge-base',
                'description' => 'Manage Category',
            ],
            'admin.knowledge-base.category.list' => [
                'parent' => 'admin.knowledge-base.category',
                'description' => 'List of Category',
            ],
            'admin.knowledge-base.category.add' => [
                'parent' => 'admin.knowledge-base.category',
                'description' => 'Add Knowledge Base',
            ],
            'admin.knowledge-base.category.update' => [
                'parent' => 'admin.knowledge-base.category',
                'description' => 'Update Category',
            ],
            'admin.knowledge-base.category.visibility' => [
                'parent' => 'admin.knowledge-base.category',
                'description' => 'Enable/Disable Knowledge Base',
            ],
            'admin.knowledge-base.category.delete' => [
                'parent' => 'admin.knowledge-base.category',
                'description' => 'Delete Category',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('category_of_knowledge_base', '{{%knowledge_base}}');

        $this->dropForeignKey('updater_of_knowledge_base', '{{%knowledge_base}}');
        $this->dropForeignKey('creator_of_knowledge_base', '{{%knowledge_base}}');

        $this->dropForeignKey('updater_of_knowledge_base_category', '{{%knowledge_base_category}}');
        $this->dropForeignKey('creator_of_knowledge_base_category', '{{%knowledge_base_category}}');

        $this->dropTable('{{%knowledge_base}}');
        $this->dropTable('{{%knowledge_base_category}}');

        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        if (!$auth->uninstallPermissions($this->permissions())) {
            return false;
        }

    }
}
