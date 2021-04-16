<?php namespace modules\calendar\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\account\models\queries\StaffQuery;
use modules\account\models\Staff;
use modules\calendar\models\queries\EventMemberQuery;
use modules\calendar\models\queries\EventQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use Throwable;
use Yii;
use yii\base\Event as EventDispatcher;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\StaleObjectException;

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
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && in_array($this->scenario, ['admin/event/add', 'admin/add'])) {
            $this->recordSavedHistory();
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $this->recordDeletedHistory();
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

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'event_id', 'staff_id']);

        return array_merge($params, [
            'staff_name' => $this->staff->name,
            'event_name' => $this->event->name,
        ]);
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function recordSavedHistory()
    {
        return Account::history()->save('event_member.add', [
            'params' => $this->getHistoryParams(),
            'description' => 'Inviting {staff_name} to event "{event_name}"',
            'tag' => 'invite',
            'model' => Event::class,
            'model_id' => $this->event_id,
        ]);
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function recordDeletedHistory()
    {
        return Account::history()->save('event_member.delete', [
            'params' => $this->getHistoryParams(),
            'description' => 'Cancelling invitation of {staff_name} to event "{event_name}"',
            'tag' => 'cancel_invitation',
            'model' => Event::class,
            'model_id' => $this->event_id,
        ]);
    }

    /**
     * @param EventDispatcher $event
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public static function deleteAllMemberRelatedToDeletedStaff($event)
    {
        /** @var Staff $model */
        $model = $event->sender;
        $members = EventMember::find()->andWhere(['staff_id' => $model->id])->all();

        foreach ($members AS $member) {
            if (!$member->delete()) {
                throw new Exception('Failed to delete related event member');
            }
        }
    }
}
