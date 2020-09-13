<?php namespace modules\account\migrations;

/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Migration;
use yii\rbac\DbManager;

/**
 * Updates indexes without a prefix.
 *
 * @see    https://github.com/yiisoft/yii2/pull/15548
 *
 * @author Sergey Gonimar <sergey.gonimar@gmail.com>
 * @since  2.0.16
 */
class M180523151638RbacUpdatesIndexesWithoutPrefix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $authManager = $this->getAuthManager();

        $this->dropIndex('auth_assignment_user_id_idx', $authManager->assignmentTable);
        $this->createIndex('{{%idx-auth_assignment-user_id}}', $authManager->assignmentTable, 'user_id');

        $this->dropIndex('idx-auth_item-type', $authManager->itemTable);
        $this->createIndex('{{%idx-auth_item-type}}', $authManager->itemTable, 'type');
    }

    /**
     * @return DbManager
     * @throws InvalidConfigException
     */
    protected function getAuthManager()
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException('You should configure "authManager" components to use database before executing this migration.');
        }

        return $authManager;
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $authManager = $this->getAuthManager();

        $this->dropIndex('{{%idx-auth_assignment-user_id}}', $authManager->assignmentTable);
        $this->createIndex('auth_assignment_user_id_idx', $authManager->assignmentTable, 'user_id');


        $this->dropIndex('{{%idx-auth_item-type}}', $authManager->itemTable);
        $this->createIndex('idx-auth_item-type', $authManager->itemTable, 'type');
    }
}
