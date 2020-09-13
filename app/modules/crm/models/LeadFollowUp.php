<?php namespace modules\crm\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\Staff;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\crm\models\queries\LeadFollowUpQuery;
use modules\crm\models\queries\LeadFollowUpTypeQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 *
 * @property Lead             $lead
 * @property Staff            $staff
 * @property LeadFollowUpType $type
 *
 * @property int              $id         [int(10) unsigned]
 * @property int              $date       [int(11) unsigned]
 * @property int              $lead_id    [int(11) unsigned]
 * @property int              $staff_id   [int(11) unsigned]
 * @property int              $type_id    [int(11) unsigned]
 * @property int              $duration   [int(11) unsigned]
 * @property string           $location
 * @property string           $note
 * @property int              $created_at [int(11) unsigned]
 */
class LeadFollowUp extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lead_follow_up}}';
    }

    /**
     * @inheritdoc
     * @return LeadFollowUpQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new LeadFollowUpQuery(get_called_class());

        return $query->alias("lead_follow_up");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['date', 'staff_id', 'type_id'],
                'required',
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                'date',
                'date',
            ],
            [
                ['staff_id'],
                'exist',
                'targetRelation' => 'staff',
            ],
            [
                ['type_id'],
                'exist',
                'targetRelation' => 'type',
                'filter' => function ($query) {
                    /** @var LeadFollowUpTypeQuery $query */

                    return $query->enabled();
                },
            ],
            [
                ['note'],
                'safe',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'date' => Yii::t('app', 'Date'),
            'lead_id' => Yii::t('app', 'Lead'),
            'staff_id' => Yii::t('app', 'Staff'),
            'type_id' => Yii::t('app', 'Followed up by'),
            'duration' => Yii::t('app', 'Duration'),
            'location' => Yii::t('app', 'Location'),
            'note' => Yii::t('app', 'Note'),
            'created_at' => Yii::t('app', 'Created At'),
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
     * @return ActiveQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'staff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getLead()
    {
        return $this->hasOne(Lead::class, ['id' => 'lead_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(LeadFollowUpType::class, ['id' => 'type_id']);
    }
}
