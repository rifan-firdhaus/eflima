<?php namespace modules\crm\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\account\models\AccountComment;
use modules\account\models\queries\AccountCommentQuery;
use modules\account\models\queries\StaffQuery;
use modules\account\models\Staff;
use modules\address\models\Country;
use modules\address\models\queries\CountryQuery;
use modules\calendar\models\Event as CalendarEvent;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\components\Setting;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\crm\models\queries\CustomerQuery;
use modules\crm\models\queries\LeadFollowUpQuery;
use modules\crm\models\queries\LeadQuery;
use modules\note\models\Note;
use modules\task\models\query\TaskAssigneeQuery;
use modules\task\models\Task;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\Exception as DbException;
use yii\db\StaleObjectException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property string                $name
 * @property LeadSource            $source
 * @property LeadStatus            $status
 * @property Staff[]               $assignees
 * @property Country               $country
 * @property LeadAssignee[]        $assigneesRelationship
 * @property LeadFollowUp[]        $followUps
 * @property-read AccountComment[] $comments
 *
 * @property string                $fullAddress
 * @property array                 $historyParams
 * @property-read LeadFollowUp     $lastFollowUp
 * @property-read Customer         $customer
 *
 * @property int                   $id           [int(10) unsigned]
 * @property int                   $customer_id  [int(11) unsigned]
 * @property int                   $status_id    [int(11) unsigned]
 * @property int                   $source_id    [int(11) unsigned]
 * @property string                $company
 * @property string                $first_name
 * @property string                $last_name
 * @property string                $phone
 * @property string                $email
 * @property string                $mobile
 * @property string                $city
 * @property string                $province
 * @property string                $country_code [char(3)]
 * @property string                $postal_code
 * @property string                $address
 * @property int                   $order        [int(11) unsigned]
 * @property int                   $creator_id   [int(11) unsigned]
 * @property int                   $created_at   [int(11) unsigned]
 * @property int                   $updater_id   [int(11) unsigned]
 * @property int                   $updated_at   [int(11) unsigned]
 */
class Lead extends ActiveRecord
{
    public $assignee_ids = [];
    public $assignor_id;

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

        $behaviors['blamable'] = [
            'class' => BlameableBehavior::class,
            'createdByAttribute' => 'creator_id',
            'updatedByAttribute' => 'updater_id',
        ];

        $behaviors['attributeTypecast'] = [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'status_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                'source_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                'customer_id' => AttributeTypecastBehavior::TYPE_INTEGER,
            ],
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
     * @return ActiveQuery|LeadFollowUpQuery
     */
    public function getLastFollowUp()
    {
        return $this->hasOne(LeadFollowUp::class, ['lead_id' => 'id'])->orderBy(['date' => SORT_DESC])->alias('last_follow_up_of_lead');
    }

    /**
     * @return ActiveQuery|AccountCommentQuery
     */
    public function getComments()
    {
        return $this->hasMany(AccountComment::class, ['model_id' => 'id'])->andOnCondition(['model' => 'lead']);
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
     * @return ActiveQuery|CustomerQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
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
    public function loadDefaultValues($skipIfSet = true)
    {
        if (!$skipIfSet || empty($this->status_id)) {
            /** @var Setting $setting */
            $setting = Yii::$app->setting;

            $this->status_id = $setting->get('lead/default_status');
        }

        return parent::loadDefaultValues($skipIfSet);
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
     * @inheritDoc
     */
    public function beforeDelete()
    {
        $this->deleteRelations();

        return parent::beforeDelete();
    }

    /**
     * @throws DbException
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function deleteRelations()
    {
        foreach ($this->assigneesRelationship AS $assignee) {
            if (!$assignee->delete()) {
                throw new DbException('Failed to delete assignee');
            }
        }

        foreach ($this->followUps AS $followUp) {
            if (!$followUp->delete()) {
                throw new DbException('Failed to delete related follow up');
            }
        }

        if (Yii::$app->hasModule('task')) {
            $tasks = Task::find()->andWhere(['model' => 'lead', 'model_id' => $this->id])->all();

            foreach ($tasks AS $task) {
                if (!$task->delete()) {
                    throw new DbException('Failed to delete related tasks');
                }
            }
        }

        if (Yii::$app->hasModule('calendar')) {
            $events = CalendarEvent::find()->andWhere(['model' => 'lead', 'model_id' => $this->id])->all();

            foreach ($events AS $event) {
                if (!$event->delete()) {
                    throw new DbException('Failed to delete related event');
                }
            }
        }

        if (Yii::$app->hasModule('note')) {
            $notes = Note::find()->andWhere(['model' => 'lead', 'model_id' => $this->id])->all();

            foreach ($notes AS $note) {
                if (!$note->delete()) {
                    throw new DbException('Failed to delete related note');
                }
            }
        }

        foreach ($this->comments AS $comment) {
            if (!$comment->delete()) {
                throw new DbException('Failed to delete related comments');
            }
        }
    }

    /**
     * @return bool
     *
     * @throws Throwable
     * @throws StaleObjectException
     */
    protected function saveAssignees()
    {
        /** @var LeadAssignee[] $currentModels */
        $currentModels = $this->getAssigneesRelationship()
            ->indexBy('assignee_id')
            ->select('assignee_id')
            ->asArray()
            ->all();

        foreach ($this->assignee_ids AS $assigneeId) {
            if (isset($currentModels[$assigneeId])) {
                continue;
            }

            if (!$this->assign($assigneeId, $this->assignor_id, false)) {
                return false;
            }
        }

        $addedModels = $this->getAssigneesRelationship()
            ->andWhere(['NOT IN', 'assignee_id', array_keys($currentModels)])
            ->all();

        LeadAssignee::sendAssignNotification($addedModels, $this, $this->assignor_id);

        foreach ($currentModels AS $key => $model) {
            if (in_array($key, $this->assignee_ids)) {
                continue;
            }

            if (!$this->unassign($model['assignee_id'])) {
                return false;
            }

        }

        return true;
    }

    /**
     * @param Staff|string|int $assignee
     * @param Staff|string|int $assignor
     * @param bool             $notify
     *
     * @return bool
     *
     * @throws DbException
     * @throws InvalidConfigException
     */
    public function assign($assignee, $assignor, $notify = true)
    {
        if (!$assignor instanceof Staff) {
            $assignor = Staff::find()->andWhere(['id' => $assignor])->one();

            if (!$assignor) {
                throw new Exception('Invalid assignor');
            }
        }

        if (!$assignee instanceof Staff) {
            $assignee = Staff::find()->andWhere(['id' => $assignee])->one();

            if (!$assignor) {
                throw new Exception('Invalid assignee');
            }
        }

        $model = new LeadAssignee([
            'scenario' => 'admin/lead/add',
            'lead_id' => $this->id,
            'assignee_id' => $assignee->id,
            'assignor_id' => $assignor->id,
        ]);

        $model->loadDefaultValues();

        if (!$model->save()) {
            return false;
        }

        if ($notify) {
            LeadAssignee::sendAssignNotification([$model], $this, $assignor);
        }

        return true;
    }

    /**
     * @param Customer $customer
     *
     * @return bool
     *
     * @throws InvalidConfigException
     */
    public function converted($customer)
    {
        if (!empty($this->customer_id)) {
            return false;
        }

        /** @var Setting $setting */
        $setting = Yii::$app->setting;

        $this->customer_id = $customer->id;
        $this->status_id = $setting->get('lead/converted_status');

        return $this->save(false);
    }

    /**
     * @param $staff
     *
     * @return bool|false|int
     *
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function unassign($staff)
    {
        $staffId = $staff instanceof Staff ? $staff->id : $staff;

        $model = $this->getAssigneesRelationship()->andWhere(['assignee_id' => $staffId])->one();

        if (!$model) {
            return false;
        }

        return $model->delete();
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
        /** @var Setting $setting */
        $setting = Yii::$app->setting;

        $history = [
            'params' => $this->getHistoryParams(),
            'tag' => 'update',
            'model' => Lead::class,
            'model_id' => $this->id,
        ];

        if ($this->status == $setting->get('lead/converted_status') && $this->customer_id) {
            $history['description'] = 'Converting lead "{name}" to customer "{customer_name}"';
            $history['params']['customer_name'] = $this->customer->name;
        } else {
            $history['description'] = 'Changing status of lead "{name}" to {status_label}';
        }

        return Account::history()->save('lead.status', $history);
    }


    /**
     * @param int[]|string[] $ids
     *
     * @return bool
     *
     * @throws Throwable
     */
    public static function bulkDelete($ids)
    {
        if (empty($ids)) {
            return true;
        }

        $transaction = self::getDb()->beginTransaction();

        try {
            $query = self::find()->andWhere(['id' => $ids]);

            foreach ($query->each(10) AS $lead) {
                if (!$lead->delete()) {
                    $transaction->rollBack();

                    return false;
                }
            }

            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }
}
