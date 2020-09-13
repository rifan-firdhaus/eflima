<?php namespace modules\finance\migrations;

use modules\core\db\MigrationSettingInstaller;
use Yii;
use yii\db\Migration;

/**
 * Class M190404185319Finance
 */
class M190404185319Finance extends Migration
{
    use MigrationSettingInstaller;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%currency}}", [
            'code' => $this->char(3)->notNull(),
            'name' => $this->text()->append('CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL'),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'symbol' => $this->text()->append('CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL'),
        ], $tableOptions);

        $this->createTable('{{%tax}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->text()->notNull(),
            'rate' => $this->decimal(8, 5)->defaultValue(0)->notNull(),
            'description' => $this->text()->null(),
            'is_enabled' => $this->boolean()->defaultValue(true),
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->addPrimaryKey('primary_index', '{{%currency}}', 'code');

        $this->addColumn('{{%customer}}', 'currency_code', $this->char(3)->after('vat_number')->null());

        $this->registerDefaults();
        $this->registerSettings();
    }

    public function registerDefaults()
    {
        $time = time();
        $this->beginCommand('Register currencies');
        $currencies = include Yii::getAlias("@modules/finance/data/currencies.php");
        Yii::$app->db->createCommand()->batchInsert('{{%currency}}', ['name', 'code', 'symbol','is_enabled'], $currencies)->execute();
        $this->endCommand($time);
    }

    /**
     * @return array
     */
    public function settings()
    {
        return [
            [
                'id' => 'finance/base_currency',
                'value' => 'USD',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tax}}');
        $this->dropTable('{{%currency}}');

        $this->dropColumn("{{%customer}}", 'currency_code');

        $this->unregisterSettings();

        return true;
    }
}
