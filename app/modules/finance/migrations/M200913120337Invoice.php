<?php

namespace modules\finance\migrations;

use yii\db\Migration;

/**
 * Class M200913120337Invoice
 */
class M200913120337Invoice extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%invoice_item}}', 'order', $this->smallInteger(5)->unsigned()->null()->defaultValue(0)->after('params')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%invoice_item}}', 'order');
    }
}
