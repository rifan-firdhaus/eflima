<?php namespace modules\account\migrations;

use modules\core\db\MigrationSettingInstaller;
use yii\db\Migration;

/**
 * Class M181013053459Account
 */
class M181013053459Account extends Migration
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

        $this->createTable('{{%account}}', [
            'id' => $this->primaryKey()->unsigned(),
            'username' => $this->text()->append('CHARACTER SET utf8 COLLATE utf8_bin'),
            'email' => $this->text(),
            'password' => $this->text()->append('CHARACTER SET utf8 COLLATE utf8_bin NOT NULL'),
            'type' => $this->string(16),
            'is_blocked' => $this->boolean()->notNull()->defaultValue(0),
            'avatar' => $this->text()->null(),
            'access_token' => $this->text()->append('CHARACTER SET utf8 COLLATE utf8_bin'),
            'auth_key' => $this->text()->append('CHARACTER SET utf8 COLLATE utf8_bin'),
            'password_reset_token' => $this->text()->append('CHARACTER SET utf8 COLLATE utf8_bin'),
            'password_reset_token_expired_at' => $this->integer()->unsigned()->null(),
            'last_activity_at' => $this->integer()->unsigned()->null(),
            'confirmed_at' => $this->integer()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%account_preference}}', [
            'id' => $this->primaryKey()->unsigned(),
            'account_id' => $this->integer()->unsigned()->notNull(),
            'key' => $this->string(),
            'value' => $this->text()->null(),
            'updated_at' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createTable('{{%account_contact}}', [
            'id' => $this->primaryKey()->unsigned(),
            'account_id' => $this->integer()->unsigned()->notNull(),
            'phone' => $this->text()->null(),
            'mobile' => $this->text()->null(),
            'city_name' => $this->text()->null(),
            'city_id' => $this->integer()->unsigned()->null(),
            'province_name' => $this->text()->null(),
            'province_code' => $this->string()->null(),
            'country_code' => $this->char(3)->null(),
            'postal_code' => $this->text()->null(),
            'address' => $this->text()->null(),
            'facebook' => $this->text()->null(),
            'twitter' => $this->text()->null(),
            'instagram' => $this->text()->null(),
            'pinterest' => $this->text()->null(),
            'linkedin' => $this->text()->null(),
            'whatsapp' => $this->text()->null(),
            'line' => $this->text()->null(),
            'wechat' => $this->text()->null(),
            'telegram' => $this->text()->null(),
            'github' => $this->text()->null(),
        ], $tableOptions);

        $this->createTable('{{%account_session}}', [
            'id' => $this->primaryKey()->unsigned(),
            'account_id' => $this->integer()->unsigned()->notNull(),
            'session_id' => $this->text()->append('CHARACTER SET utf8 COLLATE utf8_bin NOT NULL'),
            'user_agent' => $this->text()->null(),
            'ip' => $this->text()->null(),
            'logged_in_at' => $this->integer()->unsigned(),
            'logged_out_at' => $this->integer()->unsigned(),
            'last_activity_at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%account_notification}}', [
            'id' => $this->char(16),
            'title' => $this->text(),
            'body' => $this->text(),
            'body_params' => $this->text()->null(),
            'title_params' => $this->text()->null(),
            'url' => $this->text()->null(),
            'is_internal_url' => $this->boolean()->defaultValue(1),
            'data' => $this->text()->null(),
            'receiver_type' => $this->char(1)->notNull(),
            'category' => $this->string(64)->null(),
            'at' => $this->integer()->unsigned(),
        ], $tableOptions);

        $this->createTable('{{%account_notification_receiver}}', [
            'id' => $this->primaryKey()->unsigned(),
            'notification_id' => $this->char(16)->notNull(),
            'account_type' => $this->string(16)->null(),
            'account_id' => $this->integer()->unsigned()->null(),
            'is_read' => $this->boolean()->defaultValue(0),
            'is_seen' => $this->boolean()->defaultValue(0),
        ], $tableOptions);

        $this->addPrimaryKey('index', '{{%account_notification}}', 'id');

        $this->addForeignKey(
            'session_of_account',
            '{{%account_session}}', 'account_id',
            '{{%account}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'preference_of_account',
            '{{%account_preference}}', 'account_id',
            '{{%account}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'contact_of_account',
            '{{%account_contact}}', 'account_id',
            '{{%account}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'province_of_contact',
            '{{account_contact}}', 'province_code',
            '{{province}}', 'code',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'city_of_contact',
            '{{account_contact}}', 'city_id',
            '{{city}}', 'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'account_of_notification_receiver',
            '{{%account_notification_receiver}}', 'account_id',
            '{{%account}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'notification_of_receiver',
            '{{%account_notification_receiver}}', 'notification_id',
            '{{%account_notification}}', 'id',
            'CASCADE',
            'CASCADE'
        );

        return $this->registerSettings();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('session_of_account', '{{%account_session}}');
        $this->dropForeignKey('preference_of_account', '{{%account_preference}}');
        $this->dropForeignKey('contact_of_account', '{{%account_contact}}');
        $this->dropForeignKey('province_of_contact', "{{%account_contact}}");
        $this->dropForeignKey('city_of_contact', "{{%account_contact}}");
        $this->dropForeignKey('account_of_notification_receiver', "{{%account_notification_receiver}}");
        $this->dropForeignKey('notification_of_receiver', "{{%account_notification_receiver}}");

        $this->dropTable('{{%account}}');
        $this->dropTable('{{%account_preference}}');
        $this->dropTable('{{%account_contact}}');
        $this->dropTable('{{%account_session}}');
        $this->dropTable("{{%account_notification}}");
        $this->dropTable("{{%account_notification_receiver}}");

        $this->unregisterSettings();

        return true;
    }

    /**
     * @return array
     */
    public function settings()
    {
        return [
            [
                'id' => 'backend/is_captcha_on_login_enabled',
                'value' => 1,
            ],
            [
                'id' => 'backend/is_reset_password_request_enabled',
                'value' => 1,
            ],
            [
                'id' => 'backend/is_two_factor_login_enabled',
                'value' => 0,
            ],
            [
                'id' => 'is_session_log_enabled',
                'value' => 1,
                'is_autoload' => true,
            ],
            [
                'id' => 'session_log_size',
                'value' => 500,
            ],
            [
                'id' => 'backend/is_login_in_multi_device_allowed',
                'value' => 1,
            ],
            [
                'id' => 'company/name',
                'value' => 'Eflima'
            ],
            [
                'id' => 'company/address',
                'value' => 'Jl. Mojopahit, Miji Lama Gg Baru No.2'
            ],
            [
                'id' => 'company/city',
                'value' => 'Mojokerto'
            ],
            [
                'id' => 'company/province',
                'value' => 'Province'
            ],
            [
                'id' => 'company/country_code',
                'value' => 'ID'
            ],
            [
                'id' => 'company/postal_code',
                'value' => '0647857'
            ],
            [
                'id' => 'company/phone',
                'value' => '+6285649303689'
            ],
            [
                'id' => 'company/vat_number',
                'value' => '9876545678'
            ],
        ];
    }
}
