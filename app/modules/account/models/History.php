<?php

namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\queries\HistoryQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use Yii;
use function is_array;
use function json_encode;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Account      $executor
 *
 * @property int          $id          [int(10) unsigned]
 * @property int          $executor_id [int(11) unsigned]
 * @property string       $model
 * @property string       $model_id
 * @property string       $key         [varchar(64)]
 * @property string|array $params
 * @property string       $description
 * @property string       $tag
 * @property int          $at          [int(11) unsigned]
 */
class History extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%history}}';
    }

    /**
     * @inheritdoc
     * @return HistoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new HistoryQuery(get_called_class());

        return $query->alias("history");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['executor_id'], 'integer'],
            [['at'], 'double'],
            [['key'], 'required'],
            [['description', 'tag'], 'string'],
            [['key'], 'string', 'max' => 64],
            [['executor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['executor_id' => 'id']],
            [['params'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'executor_id' => Yii::t('app', 'Executor ID'),
            'key' => Yii::t('app', 'Key'),
            'params' => Yii::t('app', 'Params'),
            'description' => Yii::t('app', 'Description'),
            'tag' => Yii::t('app', 'Tag'),
            'at' => Yii::t('app', 'At'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getExecutor()
    {
        return $this->hasOne(Account::class, ['id' => 'executor_id'])->alias('executor_of_history');
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->params = is_array($this->params) ? json_encode($this->params) : $this->params;

        return true;
    }
}
