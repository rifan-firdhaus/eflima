<?php namespace modules\crm\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\account\models\Staff;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\crm\models\queries\LeadAssigneeQuery;
use modules\task\models\Task;
use Throwable;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

/**
 * @author Rifan Firdhaus Widigdo  <rifanfirdhaus@gmail.com>
 *
 * @property Staff $assignee
 * @property Staff $assignor
 * @property Lead  $lead
 *
 * @property int   $id          [int(10) unsigned]
 * @property int   $lead_id     [int(11) unsigned]
 * @property int   $assignee_id [int(11) unsigned]
 * @property int   $assigned_at [int(11) unsigned]
 * @property int   $assignor_id [int(11) unsigned]
 */
class LeadAssignee extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lead_assignee}}';
    }

    /**
     * @inheritdoc
     *
     * @return LeadAssigneeQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new LeadAssigneeQuery(get_called_class());

        return $query->alias("lead_assignee");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['lead_id', 'assignee_id'],
                'required',
                'on' => ['admin/add', 'admin/update', 'admin/lead/add'],
            ],
            [
                'assignee_id',
                'exist',
                'skipOnError' => true,
                'targetRelation' => 'assignee',
            ],
            [
                'lead_id',
                'exist',
                'skipOnError' => true,
                'targetRelation' => 'lead',
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
            'createdAtAttribute' => 'assigned_at',
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
            'lead_id' => Yii::t('app', 'Lead ID'),
            'assignee_id' => Yii::t('app', 'Assignee ID'),
            'assigned_at' => Yii::t('app', 'Assigned At'),
            'assignor_id' => Yii::t('app', 'Assignor ID'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAssignee()
    {
        return $this->hasOne(Staff::class, ['id' => 'assignee_id'])->alias('profile_of_assignee');
    }

    /**
     * @return ActiveQuery
     */
    public function getAssignor()
    {
        return $this->hasOne(Staff::class, ['id' => 'assignor_id'])->alias('assignor_of_assignee');
    }

    /**
     * @return ActiveQuery
     */
    public function getLead()
    {
        return $this->hasOne(Lead::class, ['id' => 'lead_id'])->alias('lead_of_assignee');
    }


    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && in_array($this->scenario, ['admin/lead/add', 'admin/add'])) {
            $this->recordAssignedHistory();
        }
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function recordAssignedHistory()
    {
        return Account::history()->save('lead_assignee.add', [
            'params' => $this->getHistoryParams(),
            'description' => 'Assigning {assignee_name} to lead "{lead_name}"',
            'tag' => 'assign',
            'model' => Lead::class,
            'model_id' => $this->lead_id,
        ]);
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['assignee_id', 'assignor_id', 'lead_id']);

        return array_merge($params, [
            'assignee_name' => $this->assignee->name,
            'assignor_name' => $this->assignor->name,
            'lead_name' => $this->lead->name,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $this->recordRemoveAssignementHistory();
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function recordRemoveAssignementHistory()
    {
        return Account::history()->save('lead_assignee.delete', [
            'params' => $this->getHistoryParams(),
            'description' => 'Removing assignment of {assignee_name} from lead "{lead_name}"',
            'tag' => 'release_assignment',
            'model' => Lead::class,
            'model_id' => $this->lead_id
        ]);
    }

}
