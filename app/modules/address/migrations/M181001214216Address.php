<?php namespace modules\address\migrations;

use Yii;
use yii\db\Migration;
use yii\helpers\FileHelper;

/**
 * Class M190325214217Address
 */
class M181001214216Address extends Migration
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

        $this->createTable('{{%country}}', [
            'code' => $this->char(3),
            'iso2' => $this->char(2)->unique(),
            'name' => $this->text(),
            'phone_code' => $this->text()->null(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'continent_code' => $this->char(2),
            'currency_code' => $this->char(3)->null(),
        ], $tableOptions);

        $this->createTable('{{%province}}', [
            'code' => $this->string(255),
            'country_code' => $this->char(3),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'name' => $this->text(),
        ], $tableOptions);

        $this->createTable('{{%city}}', [
            'id' => $this->primaryKey()->unsigned(),
            'province_code' => $this->string(255),
            'code' => $this->string(255)->null(),
            'is_enabled' => $this->boolean()->defaultValue(1),
            'name' => $this->text(),
        ], $tableOptions);

        $this->addPrimaryKey('primary_index', '{{%country}}', 'code');
        $this->addPrimaryKey('primary_index', '{{%province}}', 'code');

        $this->addForeignKey(
            'country_of_province',
            '{{%province}}', 'country_code',
            '{{%country}}', 'code'
        );

        $this->addForeignKey(
            'province_of_city',
            '{{%city}}', 'province_code',
            '{{%province}}', 'code'
        );

        $this->registerDefaults();
    }

    public function registerDefaults()
    {
        // Register Countries
        $time = time();
        $this->beginCommand('Register countries');
        $countries = include "data/countries.php";
        Yii::$app->db->createCommand()->batchInsert('{{%country}}', ['code', 'iso2', 'name', 'phone_code', 'currency_code', 'continent_code'], array_values($countries))->execute();
        $this->endCommand($time);

        // Register Provinces
        $provinceFiles = FileHelper::findFiles(Yii::getAlias('@modules/address/migrations/data/provinces'));

        foreach ($provinceFiles AS $provinceFile) {
            $time = time();
            $this->beginCommand('Register provinces of ' . basename($provinceFile));
            $provinces = include $provinceFile;
            Yii::$app->db->createCommand()->batchInsert('{{%province}}', ['code', 'name', 'country_code'], array_values($provinces))->execute();
            $this->endCommand($time);
        }

        // Register Cities
        $cityFiles = FileHelper::findFiles(Yii::getAlias('@modules/address/migrations/data/cities'));

        foreach ($cityFiles AS $cityFile) {
            $time = time();
            $this->beginCommand('Register cities of ' . basename($cityFile));
            $cities = include $cityFile;
            Yii::$app->db->createCommand()->batchInsert('{{%city}}', ['province_code', 'name'], array_values($cities))->execute();
            $this->endCommand($time);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('country_of_province', '{{%province}}');
        $this->dropForeignKey('province_of_city', '{{%city}}');

        $this->dropTable('{{%country}}');
        $this->dropTable('{{%province}}');
        $this->dropTable('{{%city}}');

        return true;
    }
}
