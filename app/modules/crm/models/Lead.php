<?php namespace modules\crm\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\account\models\queries\StaffQuery;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\address\models\Country;
use modules\address\models\queries\CountryQuery;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\crm\models\queries\LeadFollowUpQuery;
use modules\crm\models\queries\LeadQuery;
use modules\task\models\query\TaskAssigneeQuery;
use modules\task\models\TaskAssignee;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as DbException;
use yii\db\StaleObjectException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property string         $name
 * @property LeadSource     $source
 * @property LeadStatus     $status
 * @property Staff[]        $assignees
 * @property Country        $country
 * @property LeadAssignee[] $assigneesRelationship
 * @property LeadFollowUp[] $followUps
 *
 * @property int            $id           [int(10) unsigned]
 * @property int            $customer_id  [int(11) unsigned]
 * @property int            $status_id    [int(11) unsigned]
 * @property int            $source_id    [int(11) unsigned]
 * @property string         $company
 * @property string         $first_name
 * @property string         $last_name
 * @property string         $phone
 * @property string         $email
 * @property string         $mobile
 * @property string         $city
 * @property string         $province
 * @property string         $country_code [char(3)]
 * @property string         $postal_code
 * @property string         $address
 * @property int            $order        [int(11) unsigned]
 * @property int            $created_at   [int(11) unsigned]
 * @property string         $fullAddress
 * @property array          $historyParams
 * @property int            $updated_at   [int(11) unsigned]
 */
class Lead extends ActiveRecord
{
    public $assignee_ids = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lead}}';
    }

    /**
     * @inheritdoc
     *
     * @return LeadQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new LeadQuery(get_called_class());

        return $query->alias("lead");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['source_id', 'status_id', 'first_name'],
                'required',
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                'source_id',
                'exist',
                'targetRelation' => 'source',
            ],
            [
                'status_id',
                'exist',
                'targetRelation' => 'status',
            ],
            [
                'email',
                'email',
            ],
            [
                ['assignee_ids'],
                'exist',
                'skipOnError' => true,
                'allowArray' => true,
                'targetAttribute' => 'id',
                'targetClass' => Staff::class,
            ],
            [
                [
                    'last_name',
                    'address',
                    'postal_code',
                    'province',
                    'country_code',
                    'city',
                    'phone',
                    'mobile',
                ],
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
            'customer_id' => Yii::t('app', 'Customer'),
            'status_id' => Yii::t('app', 'Status'),
            'source_id' => Yii::t('app', 'Source'),
            'company' => Yii::t('app', 'Company'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'phone' => Yii::t('app', 'Phone'),
            'email' => Yii::t('app', 'Email'),
            'mobile' => Yii::t('app', 'Mobile'),
            'city' => Yii::t('app', 'City'),
            'province' => Yii::t('app', 'Province'),
            'country_code' => Yii::t('app', 'Country'),
            'postal_code' => Yii::t('app', 'Postal Code'),
            'address' => Yii::t('app', 'Address'),
            'order' => Yii::t('app', 'Order'),
            'assignee_ids' => Yii::t('app', 'Assignee'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
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

        $behaviors['attributeTypecast'] = [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'status_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                'source_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                'customer_id' => AttributeTypecastBehavior::TYPE_INTEGER,
            ]
        ];

        return $behaviors;
    }

    /**
     * @return ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(LeadSource::class, ['id' => 'source_id'])->alias('source_of_lead');
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(LeadStatus::class, ['id' => 'status_id'])->alias('status_of_lead');
    }

    /**
     * @return ActiveQuery|CountryQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code'])->alias('country_of_lead');
    }

    /**
     * @return ActiveQuery|StaffQuery
     */
    public function getAssignees()
    {
        return $this->hasMany(Staff::class, ['id' => 'assignee_id'])->via('assigneesRelationship');
    }

    /**
     * @return ActiveQuery|TaskAssigneeQuery
     */
    public function getAssigneesRelationship()
    {
        return $this->hasMany(LeadAssignee::class, ['lead_id' => 'id'])->alias('assignees_of_lead');
    }

    /**
     * @return ActiveQuery|LeadFollowUpQuery
     */
    public function getFollowUps()
    {
        return $this->hasMany(LeadFollowUp::class, ['lead_id' => 'id'])->alias('follow_ups_of_lead');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return implode(' ', array_filter([$this->first_name, $this->last_name]));
    }

    /**
     * @return string
     */
    public function getFullAddress()
    {
        $components = $this->getAttributes(['address', 'city', 'province']);

        if ($this->country_code) {
            $components['country'] = $this->country->name;
        }

        $components = array_filter($components);

        return implode(', ', $components);
    }

    /**
     * @param integer $statusId
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function changeStatus($statusId)
    {
        if (!LeadStatus::find()->andWhere(['id' => $statusId])->enabled()->exists()) {
            $this->addError('status_id', Yii::t('app', '{object} doesn\'t exists', [
                'object' => Yii::t('app', 'Status'),
            ]));

            return false;
        }

        if ($this->status_id == $statusId) {
            return true;
        }

        $this->status_id = $statusId;

        if (!$this->save(false)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $isManualUpdate = in_array($this->scenario, ['admin/add', 'admin/update']);
        $realChangedAttributes = $changedAttributes;

        unset(
            $realChangedAttributes['assignee_ids'],
            $realChangedAttributes['status_id'],
            $realChangedAttributes['updated_at'],
        );

        // Save assignee
        if ($this->assignee_ids && $isManualUpdate) {
            if (!$this->saveAssignees()) {
                throw new DbException('Failed to assign task');
            }

            $this->assignee_ids = $this->getAssigneesRelationship()->select('assignees_of_lead.assignee_id')->createCommand()->queryColumn();
        }

        // Set History
        if (
            $isManualUpdate &&
            !empty($realChangedAttributes)
        ) {
            $this->recordSavedHistory($insert);
        }

        // Set changed status history
        if (array_key_exists('status_id', $changedAttributes)) {
            $this->recordStatusChangedHistory();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws StaleObjectException
     */
    protected function saveAssignees()
    {
        /** @var TaskAssignee[] $currentModels */
        $currentModels = $this->getAssigneesRelationship()->indexBy('assignee_id')->all();

        $addedAssignees = [];

        foreach ($this->assignee_ids AS $assigneeId) {
            if (isset($currentModels[$assigneeId])) {
                continue;
            }

            /** @var StaffAccount $account */
            $account = Yii::$app->user->identity;

            $model = new LeadAssignee([
                'scenario' => 'admin/lead/add',
                'lead_id' => $this->id,
                'assignee_id' => $assigneeId,
                'assignor_id' => $account->profile->id,
            ]);

            $model->loadDefaultValues();

            if (!$model->save()) {
                return false;
            }

            $addedAssignees[] = $model;
        }


        foreach ($currentModels AS $key => $model) {
            if (in_array($key, $this->assignee_ids)) {
                continue;
            }

            if (!$model->delete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param bool $insert
     *
     * @return bool
     * @throws DbException
     * @throws Throwable
     */
    public function recordSavedHistory($insert = false)
    {
        $history = [
            'params' => $this->getHistoryParams(),
        ];

        if ($this->scenario === 'admin/add' && $insert) {
            $history['description'] = 'Adding lead "{name}"';
        } else {
            $history['description'] = 'Updating lead "{name}"';
        }

        $historyEvent = $this->scenario === 'admin/add' ? 'lead.add' : 'lead.update';
        $history['tag'] = $this->scenario === 'admin/add' ? 'add' : 'update';

        $history['model'] = Lead::class;
        $history['model_id'] = $this->id;

        return Account::history()->save($historyEvent, $history);
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'name', 'status_id']);
        $params['status_label'] = $this->status->label;

        return $params;
    }

    /**
     * @return bool
     * @throws DbException
     * @throws Throwable
     */
    public function recordStatusChangedHistory()
    {
        $history = [
            'params' => $this->getHistoryParams(),
            'description' => 'Changing status of lead "{name}" to {status_label}',
            'tag' => 'update',
            'model' => Lead::class,
            'model_id' => $this->id
        ];

        return Account::history()->save('lead.status', $history);
    }
}
