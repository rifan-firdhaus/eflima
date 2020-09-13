<?php

namespace modules\task\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\Staff;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\task\models\query\TaskFollowerQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Staff $follower
 *
 * @property Task          $task
 * @property int           $id                                     [int(10) unsigned]
 * @property int           $task_id                                [int(11) unsigned]
 * @property int           $follower_id                            [int(11) unsigned]
 * @property bool          $is_notified_when_timer_start           [tinyint(1) unsigned]
 * @property bool          $is_notified_when_timer_end             [tinyint(1) unsigned]
 * @property bool          $is_notified_when_comment               [tinyint(1) unsigned]
 * @property bool          $is_notified_only_when_customer_comment [tinyint(1) unsigned]
 * @property bool          $is_notified_only_when_progress_updated [tinyint(1) unsigned]
 * @property int           $followed_at                            [int(11) unsigned]
 *
 */
class TaskFollower extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_follower}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'follower_id'], 'required'],
            [['task_id', 'follower_id', 'followed_at'], 'integer'],
            [['follower_id'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::class, 'targetAttribute' => ['follower_id' => 'id']],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'task_id' => Yii::t('app', 'Task ID'),
            'follower_id' => Yii::t('app', 'Follower ID'),
            'followed_at' => Yii::t('app', 'Followed At'),
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
            'updatedAtAttribute' => false,
            'createdAtAttribute' => 'followed_at',
        ];

        return $behaviors;
    }

    /**
     * @return ActiveQuery
     */
    public function getFollower()
    {
        return $this->hasOne(Staff::class, ['id' => 'follower_id'])->alias('profile_of_follower');
    }

    /**
     * @return ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id'])->alias('task_of_follower');
    }

    /**
     * @inheritdoc
     *
     * @return TaskFollowerQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TaskFollowerQuery(get_called_class());

        return $query->alias("task_follower");
    }
}
