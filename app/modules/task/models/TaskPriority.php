<?php

namespace modules\task\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\models\traits\VisibilityModel;
use modules\task\models\query\TaskPriorityQuery;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Task[] $tasks
 *
 * @property int    $id          [int(10) unsigned]
 * @property string $label
 * @property string $color_label [char(7)]
 * @property bool   $is_enabled  [tinyint(1)]
 * @property string $description
 * @property int    $order       [int(3) unsigned]
 * @property int    $creator_id  [int(11) unsigned]
 * @property int    $created_at  [int(11) unsigned]
 * @property int    $updater_id  [int(11) unsigned]
 * @property int    $updated_at  [int(11) unsigned]
 */
class TaskPriority extends ActiveRecord
{
    use VisibilityModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_priority}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['label'], 'required'],
            [['label', 'description'], 'string'],
            [['order'], 'integer'],
            [['is_enabled'], 'boolean'],
            [['is_enabled'], 'default', 'value' => 1],
            [['color_label'], 'string', 'max' => 7],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
        ];

        $behaviors['blamable'] = [
            'class' => BlameableBehavior::class,
            'createdByAttribute' => 'creator_id',
            'updatedByAttribute' => 'updater_id',
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['install'] = $scenarios['default'];
        $scenarios['admin/add'] = $scenarios['default'];
        $scenarios['admin/update'] = $scenarios['admin/add'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        $transactions = parent::transactions();

        $transactions['default'] = self::OP_ALL;
        $transactions['admin/add'] = self::OP_ALL;
        $transactions['admin/update'] = self::OP_ALL;

        return $transactions;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'label' => Yii::t('app', 'Label'),
            'color_label' => Yii::t('app', 'Color Label'),
            'order' => Yii::t('app', 'Order'),
            'is_enabled' => Yii::t('app', 'Enabled'),
            'description' => Yii::t('app', 'Description'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['priority_id' => 'id'])->alias('tasks_of_priority');
    }

    /**
     * @inheritdoc
     *
     * @return TaskPriorityQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TaskPriorityQuery(get_called_class());

        return $query->alias("task_priority");
    }
}
