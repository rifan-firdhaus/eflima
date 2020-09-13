<?php

namespace modules\ui\migrations;

use modules\core\db\MigrationSettingInstaller;
use yii\db\Migration;

/**
 * Class M190613144517Pusher
 */
class M190613144517Pusher extends Migration
{
    use MigrationSettingInstaller;

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        return $this->registerSettings();
    }

    /**
     * @return array
     */
    public function settings()
    {
        return [
            [
                'id' => 'pusher/app_id',
            ],
            [
                'id' => 'pusher/app_key',
            ],
            [
                'id' => 'pusher/app_secret',
            ],
            [
                'id' => 'pusher/cluster',
            ],
            [
                'id' => 'pusher/is_enabled',
                'value' => 0,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return $this->unregisterSettings();
    }
}
