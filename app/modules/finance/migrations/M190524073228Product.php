<?php

namespace modules\finance\migrations;

use yii\db\Migration;

/**
 * Class M190524073228Product
 */
class M190524073228Product extends Migration
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

        $this->createTable('{{%product}}',[
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->text()->notNull(),
            'description' => $this->text()->null(),
            'price' => $this->decimal(25,10)->defaultValue(0),
            'is_enabled' => $this->boolean()->defaultValue(0),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned(),
        ],$tableOptions);

        $this->createTable('{{%product_tax}}', [
            'id' => $this->primaryKey()->unsigned(),
            'tax_id' => $this->integer()->unsigned()->notNull(),
            'product_id' => $this->integer()->unsigned()->notNull(),
            'rate' => $this->decimal(8, 5)->defaultValue(0)->notNull(),
            'value' => $this->decimal(25, 10)->defaultValue(0)->notNull(),
            'real_value' => $this->decimal(25, 10)->defaultValue(0)->notNull()
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product}}');
    }
}
