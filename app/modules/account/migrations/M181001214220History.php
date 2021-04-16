<?php namespace modules\account\migrations;

use modules\core\models\Setting;
use yii\db\Migration;

/**
 * Class M190319131303History
 */
class M181001214220History extends Migration
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

        $this->createTable('{{%history}}', [
            'id' => $this->primaryKey()->unsigned(),
            'executor_id' => $this->integer()->unsigned(),
            'model' => $this->text()->null(),
            'model_id' => $this->text()->null(),
            'key' => $this->string(64)->notNull(),
            'params' => $this->text()->null(),
            'description' => $this->text()->null(),
            'tag' => $this->text()->null(),
            'at' => $this->decimal(15, 4)->null(),
        ], $tableOptions);

        $this->addForeignKey(
            'executor_of_history',
            '{{%history}}', 'executor_id',
            '{{%account}}', 'id'
        );

        return $this->registerSettings();
    }

    public function registerSettings()
    {
        foreach ($this->settings() AS $setting) {
            $time = $this->beginCommand("Register \"{$setting['id']}\" setting");

            $model = new Setting($setting);

            if (!$model->save()) {
                return false;
            }

            $this->endCommand($time);
        }

        return true;
    }

    public function settings()
    {
        return [
            [
                'id' => 'is_history_log_enabled',
                'is_autoload' => true,
                'value' => 1,
            ],
            [
                'id' => 'history_log_size',
                'value' => 500,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('executor_of_history', '{{%history}}');

        $this->dropTable('{{%history}}');

        $this->unregisterSettings();

        return true;
    }

    public function unregisterSettings()
    {
        foreach ($this->settings() AS $setting) {
            $time = $this->beginCommand("Register \"{$setting['id']}\" setting");

            $model = Setting::find()->andWhere(['id' => $setting['id']])->one();

            if (!$model->delete()) {
                return false;
            }

            $this->endCommand($time);
        }

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190319_131303_history cannot be reverted.\n";

        return false;
    }
    */
}
