<?php

namespace modules\finance\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use modules\finance\models\queries\CurrencyQuery;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property string $code       [char(3)]
 * @property string $name
 * @property bool   $is_enabled [tinyint(1)]
 * @property string $symbol
 */
class Currency extends ActiveRecord
{
    use VisibilityModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%currency}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'name', 'symbol'], 'required'],
            [['name', 'symbol'], 'string'],
            [['is_enabled'], 'boolean'],
            [['code'], 'string', 'max' => 3],
            [['code'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => Yii::t('app', 'Code'),
            'name' => Yii::t('app', 'Name'),
            'is_enabled' => Yii::t('app', 'Enabled'),
            'symbol' => Yii::t('app', 'Symbol'),
        ];
    }

    /**
     * @inheritdoc
     *
     * @return CurrencyQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new CurrencyQuery(get_called_class());

        return $query->alias("currency");
    }
}
