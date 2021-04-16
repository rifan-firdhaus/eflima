<?php namespace modules\finance\migrations;

use modules\account\rbac\DbManager;
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

        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        $time = time();
        $this->beginCommand('Register permissions');

        if (!$auth->installPermissions($this->permissions())) {
            return false;
        }

        $this->endCommand($time);
    }

    public function registerDefaults()
    {
        $time = time();
        $this->beginCommand('Register currencies');
        $currencies = include Yii::getAlias("@modules/finance/data/currencies.php");
        Yii::$app->db->createCommand()->batchInsert('{{%currency}}', ['name', 'code', 'symbol', 'is_enabled'], $currencies)->execute();
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

    public function permissions()
    {
        return [
            'admin.setting.finance' => [
                'parent' => 'admin.setting',
                'description' => 'Finance Setting',
            ],
            'admin.setting.finance.general' => [
                'parent' => 'admin.setting.finance',
                'description' => 'Finance General Setting',
            ],

            'admin.setting.finance.currency' => [
                'parent' => 'admin.setting.finance',
                'description' => 'Currency',
            ],
            'admin.setting.finance.currency.list' => [
                'parent' => 'admin.setting.finance.currency',
                'description' => 'List of Currency',
            ],
            'admin.setting.finance.currency.add' => [
                'parent' => 'admin.setting.finance.currency',
                'description' => 'Add Currency',
            ],
            'admin.setting.finance.currency.update' => [
                'parent' => 'admin.setting.finance.currency',
                'description' => 'Update Currency',
            ],
            'admin.setting.finance.currency.delete' => [
                'parent' => 'admin.setting.finance.currency',
                'description' => 'Delete Currency',
            ],
            'admin.setting.finance.currency.visibility' => [
                'parent' => 'admin.setting.finance.currency',
                'description' => 'Enable/Disable Currency',
            ],

            'admin.setting.finance.tax' => [
                'parent' => 'admin.setting.finance',
                'description' => 'Tax',
            ],
            'admin.setting.finance.tax.list' => [
                'parent' => 'admin.setting.finance.tax',
                'description' => 'List of Tax',
            ],
            'admin.setting.finance.tax.add' => [
                'parent' => 'admin.setting.finance.tax',
                'description' => 'Add Tax',
            ],
            'admin.setting.finance.tax.update' => [
                'parent' => 'admin.setting.finance.tax',
                'description' => 'Update Tax',
            ],
            'admin.setting.finance.tax.delete' => [
                'parent' => 'admin.setting.finance.tax',
                'description' => 'Delete Tax',
            ],
            'admin.setting.finance.tax.visibility' => [
                'parent' => 'admin.setting.finance.tax',
                'description' => 'Enable/Disable Tax',
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

        /** @var DbManager $auth */
        $auth = Yii::$app->authManager;

        if (!$auth->uninstallPermissions($this->permissions())) {
            return false;
        }

        return true;
    }
}
