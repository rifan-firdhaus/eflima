<?php namespace modules\core\migrations;

use modules\core\db\MigrationSettingInstaller;
use Yii;
use yii\db\Migration;

/**
 * Class M181013041631Setting
 */
class M181013041631Setting extends Migration
{
    use MigrationSettingInstaller;

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%setting}}', [
            'id' => $this->string(64),
            'value' => $this->text()->null(),
            'is_file' => $this->boolean()->defaultValue(0),
            'is_autoload' => $this->boolean(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->addPrimaryKey('id', '{{%setting}}', 'id');

        return $this->registerSettings();
    }

    /**
     * @return array
     */
    public function settings()
    {
        return [
            [
                'id' => 'website_name',
                'value' => Yii::$app->name,
                'is_autoload' => true,
            ],
            [
                'id' => 'website_description',
                'is_autoload' => true,
            ],
            [
                'id' => 'timezone',
                'is_autoload' => true,
                'value' => 'Asia/Jakarta',
            ],
            [
                'id' => 'meta_description',
            ],
            [
                'id' => 'meta_keyword',
            ],
            [
                'id' => 'logo',
                'is_autoload' => true,
                'is_file' => true,
            ],
            [
                'id' => 'favicon',
                'is_autoload' => true,
                'is_file' => true,
            ],
            [
                'id' => 'number_format',
                'value' => 'period',
            ],
            [
                'id' => 'datetime_display_format',
                'value' => 'medium',
            ],
            [
                'id' => 'date_display_format',
                'value' => 'medium',
            ],
            [
                'id' => 'time_display_format',
                'value' => 'short',
            ],
            [
                'id' => 'date_input_format',
                'value' => 'php:Y-m-d',
            ],
            [
                'id' => 'time_input_format',
                'value' => 'php:H:i',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%setting}}');

        return true;
    }
}
