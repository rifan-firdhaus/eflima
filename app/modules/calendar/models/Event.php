<?php namespace modules\calendar\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\account\models\AccountComment;
use modules\account\models\queries\AccountCommentQuery;
use modules\account\models\queries\StaffQuery;
use modules\account\models\Staff;
use modules\calendar\components\EventRelation;
use modules\calendar\models\queries\EventMemberQuery;
use modules\calendar\models\queries\EventQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\core\validators\DateValidator;
use modules\note\models\Note;
use modules\task\components\TaskRelation;
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
 * @property EventMember[]         $memberRelationships
 * @property Staff[]               $members
 * @property bool                  $isStarted
 * @property null|mixed            $relatedModel
 * @property null|EventRelation    $relatedObject
 * @property-read array            $historyParams
 * @property-read AccountComment[] $comments
 *
 * @property int                   $id         [int(10) unsigned]
 * @property string                $model
 * @property string                $model_id
 * @property string                $name
 * @property string                $description
 * @property string                $location
 * @property int                   $start_date [int(11) unsigned]
 * @property int                   $end_date   [int(11) unsigned]
 * @property int                   $creator_id [int(11) unsigned]
 * @property int                   $created_at [int(11) unsigned]
 * @property int                   $updater_id [int(11) unsigned]
 * @property int                   $updated_at [int(11) unsigned]
 */
class Event extends ActiveRecord
{
    public $member_ids = [];

    protected $_relatedModel;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%event}}';
    }

    /**
     * @inheritdoc
     *
     * @return EventQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new EventQuery(get_called_class());

        return $query->alias("event");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['start_date', 'name'],
                'required',
                'on' => ['admin/add', 'admin/update', 'admin/add/update'],
            ],
            [
                'start_date',
                'daterange',
                'type' => DateValidator::TYPE_DATETIME,
                'dateTo' => 'end_date',
                'startValidation' => [
                    'tooBig' => Yii::t('app', 'Started date must be less than end date'),
                ],
                'endValidation' => [
                    'tooSmall' => Yii::t('app', 'End date must be greater than started date'),
                ],
                'except' => ['admin/update/date'],
            ],
            [
                ['member_ids'],
                'exist',
                'skipOnError' => true,
                'allowArray' => true,
                'targetAttribute' => 'id',
                'targetClass' => Staff::class,
            ],
            [
                [
                    'end_date',
                    'description',
                    'location',
                ],
                'safe',
            ],
            [
                'model',
                'in',
                'range' => array_keys(TaskRelation::map()),
            ],
            [
                'model_id',
                'validateRelatedModel',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['admin/update/date'] = ['start_date', 'end_date', 'updated_at'];

        return $scenarios;
    }

    /**
     * @throws InvalidConfigException
     */
    public function validateRelatedModel()
    {
        if ($this->hasErrors() || empty($this->model)) {
            return;
        }

        $relation = EventRelation::get($this->model);

        $relation->validate($this->getRelatedModel(), $this);
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
     * @param false|string $color
     *
     * @return array|mixed|null
     */
    public function colors($color = false)
    {
        $colors = [
            '#f2c942' => Yii::t('app', 'Yellow'),
            '#5cbc72' => Yii::t('app', 'Green'),
            '#e46572' => Yii::t('app', 'Red'),
            '#926fd0' => Yii::t('app', 'Purple'),
            '#ffbe62' => Yii::t('app', 'Orange'),
            '#56d6b0' => Yii::t('app', 'Teal'),
            '#8b49f5' => Yii::t('app', 'Indigo'),
            '#ee6ca8' => Yii::t('app', 'Pink'),
        ];

        if ($color !== false) {
            return isset($colors[$color]) ? $colors[$color] : null;
        }

        return $colors;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'model' => Yii::t('app', 'Related to'),
            'model_id' => Yii::t('app', 'Related to'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'location' => Yii::t('app', 'Location'),
            'start_date' => Yii::t('app', 'Start Date'),
            'end_date' => Yii::t('app', 'End Date'),
            'created_at' => Yii::t('app', 'Created At'),
            'member_ids' => Yii::t('app', 'Attendees'),
        ];
    }

    /**
     * @return ActiveQuery|EventMemberQuery
     */
    public function getMemberRelationships()
    {
        return $this->hasMany(EventMember::class, ['event_id' => 'id'])->alias('members_of_event');
    }

    /**
     * @return ActiveQuery|StaffQuery
     */
    public function getMembers()
    {
        return $this->hasMany(Staff::class, ['id' => 'staff_id'])->via('memberRelationships');
    }


    /**
     * @return EventRelation|null
     *
     * @throws InvalidConfigException
     */
    public function getRelatedObject()
    {
        if (empty($this->model)) {
            return null;
        }

        return EventRelation::get($this->model);
    }

    /**
     * @return mixed|null
     * @throws InvalidConfigException
     */
    public function getRelatedModel()
    {
        if (empty($this->model)) {
            return null;
        }

        if (!isset($this->_relatedModel)) {
            $this->_relatedModel = $this->getRelatedObject()->getModel($this->model_id);
        }

        return $this->_relatedModel;
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Save members
        if ($this->member_ids) {
            if (!$this->saveMembers()) {
                throw new DbException('Failed to save members');
            }

            $this->member_ids = $this->getMemberRelationships()->select('members_of_event.staff_id')->createCommand()->queryColumn();
        }

        $isManualUpdate = in_array($this->scenario, ['admin/add', 'admin/update']);


        // Set History
        if ($isManualUpdate && !empty($changedAttributes)) {
            $this->recordSavedHistory($insert);
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
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function deleteRelations()
    {
        foreach ($this->memberRelationships AS $member) {
            if (!$member->delete()) {
                throw new DbException('Failed to delete member of event');
            }
        }

        if (Yii::$app->hasModule('note')) {
            $notes = Note::find()->andWhere(['model' => 'event', 'model_id' => $this->id])->all();

            foreach ($notes AS $note) {
                if (!$note->delete()) {
                    throw new DbException('Failed to delete related note');
                }
            }
        }

        foreach ($this->comments AS $comment) {
            if (!$comment->delete()) {
                throw new Exception('Failed to delete related comments');
            }
        }
    }

    /**
     * @return bool
     */
    public function getIsStarted()
    {
        if (empty($this->end_date)) {
            return $this->start_date == time();
        }

        return $this->start_date <= time() && $this->end_date >= time();
    }

    /**
     * @return ActiveQuery|AccountCommentQuery
     */
    public function getComments()
    {
        return $this->hasMany(AccountComment::class, ['model_id' => 'id'])->andOnCondition(['model' => 'event']);
    }

    /**
     * @return bool
     *
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function saveMembers()
    {
        /** @var EventMember[] $currentModels */
        $currentModels = $this->getMemberRelationships()->indexBy('staff_id')->all();

        foreach ($this->member_ids AS $memberId) {
            if (isset($currentModels[$memberId])) {
                continue;
            }

            if (!$this->invite($memberId)) {
                return false;
            }
        }

        foreach ($currentModels AS $key => $model) {
            if (in_array($key, $this->member_ids)) {
                continue;
            }

            if (!$model->delete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int|string $staffId
     *
     * @return bool
     */
    public function invite($staffId)
    {
        $model = new EventMember([
            'scenario' => 'admin/event/add',
            'event_id' => $this->id,
            'staff_id' => $staffId,
        ]);

        $model->loadDefaultValues();

        if (!$model->save()) {
            return false;
        }

        return true;
    }


    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'name', 'start_date', 'end_date']);

        return $params;
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
            'model' => self::class,
            'model_id' => $this->id,
        ];

        if ($this->scenario === 'admin/add' && $insert) {
            $history['description'] = 'Adding event "{name}"';
        } else {
            $history['description'] = 'Updating event "{name}"';
        }

        $historyEvent = $this->scenario === 'admin/add' ? 'event.add' : 'event.update';
        $history['tag'] = $this->scenario === 'admin/add' ? 'add' : 'update';

        return Account::history()->save($historyEvent, $history);
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

            foreach ($query->each(10) AS $event) {
                if (!$event->delete()) {
                    $transaction->rollBack();

                    return false;
                }
            }

            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();

            throw $exception;
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }
}
