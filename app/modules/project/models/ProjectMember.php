<?php namespace modules\project\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\account\models\queries\StaffQuery;
use modules\account\models\Staff;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\project\models\queries\ProjectMemberQuery;
use modules\project\models\queries\ProjectQuery;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\ModelEvent;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\StaleObjectException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property-read Project $project
 * @property-read Staff   $staff
 * @property-read Staff   $inviter
 *
 * @property int          $id          [int(10) unsigned]
 * @property int          $project_id  [int(11) unsigned]
 * @property int          $staff_id    [int(11) unsigned]
 * @property int          $inviter_id  [int(11) unsigned]
 * @property int          $invited_at  [int(11) unsigned]
 */
class ProjectMember extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%project_member}}';
    }

    /**
     * @inheritdoc
     *
     * @return ProjectMemberQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new ProjectMemberQuery(get_called_class());

        return $query->alias("project_member");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['staff_id', 'project_id', 'inviter_id'],
                'required',
                'on' => ['admin/add', 'admin/update', 'admin/project/add'],
            ],
            [
                'staff_id',
                'exist',
                'targetRelation' => 'staff',
            ],
            [
                'inviter_id',
                'exist',
                'targetRelation' => 'inviter',
            ],
            [
                'project_id',
                'exist',
                'targetRelation' => 'project',
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
            'project_id' => Yii::t('app', 'Project'),
            'staff_id' => Yii::t('app', 'Staff'),
            'assigned_at' => Yii::t('app', 'Assigned At'),
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
            'createdAtAttribute' => 'invited_at',
            'updatedAtAttribute' => false,
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && in_array($this->scenario, ['admin/project/add', 'admin/add'])) {
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
     * @return ActiveQuery|ProjectQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'project_id'])->alias('member_of_project');
    }

    /**
     * @return ActiveQuery|StaffQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'staff_id'])->alias('staff_of_project_member');
    }

    /**
     * @return ActiveQuery|StaffQuery
     */
    public function getInviter()
    {
        return $this->hasOne(Staff::class, ['id' => 'staff_id'])->alias('inviter_of_project_member');
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'project_id', 'staff_id']);

        return array_merge($params, [
            'staff_name' => $this->staff->name,
            'project_name' => $this->project->name,
        ]);
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function recordSavedHistory()
    {
        return Account::history()->save('project_member.add', [
            'params' => $this->getHistoryParams(),
            'description' => 'Inviting {staff_name} to project "{project_name}"',
            'tag' => 'invite',
            'model' => Project::class,
            'model_id' => $this->project_id,
        ]);
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function recordDeletedHistory()
    {
        return Account::history()->save('project_member.delete', [
            'params' => $this->getHistoryParams(),
            'description' => 'Removing {staff_name} from member of project "{project_name}"',
            'tag' => 'cancel_invitation',
            'model' => Project::class,
            'model_id' => $this->project_id,
        ]);
    }


    /**
     * @param ModelEvent $event
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public static function deleteAllMemberRelatedToDeletedStaff($event)
    {
        /** @var Staff $model */
        $staff = $event->sender;

        $members = ProjectMember::find()
            ->andWhere([
                'OR',
                ['inviter_id' => $staff->id],
                ['staff_id' => $staff->id],
            ])
            ->all();

        foreach ($members AS $member) {
            if (!$member->delete()) {
                throw new Exception('Failed to delete related project member');
            }
        }
    }
}
