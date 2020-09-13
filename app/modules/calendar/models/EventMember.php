<?php namespace modules\calendar\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\queries\StaffQuery;
use modules\account\models\Staff;
use modules\calendar\models\queries\EventMemberQuery;
use modules\calendar\models\queries\EventQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Event $event
 * @property Staff $staff
 *
 * @property int   $id         [int(10) unsigned]
 * @property int   $event_id   [int(11) unsigned]
 * @property int   $staff_id   [int(11) unsigned]
 * @property int   $created_at [int(11) unsigned]
 */
class EventMember extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%event_member}}';
    }

    /**
     * @inheritdoc
     *
     * @return EventMemberQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new EventMemberQuery(get_called_class());

        return $query->alias("event_member");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['staff_id'],
                'required',
                'on' => 'admin/event/add',
            ],
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
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'event_id' => Yii::t('app', 'Event ID'),
            'staff_id' => Yii::t('app', 'Staff ID'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return ActiveQuery|EventQuery
     */
    public function getEvent()
    {
        return $this->hasOne(Event::class, ['id' => 'event_id'])->alias('event_of_member');
    }

    /**
     * @return ActiveQuery|StaffQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'staff_id'])->alias('staff_of_event');
    }
}
