<?php namespace modules\project\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\queries\StaffQuery;
use modules\account\models\Staff;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\project\models\queries\ProjectMemberQuery;
use modules\project\models\queries\ProjectQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Project $project
 * @property Staff   $staff
 *
 * @property int     $id         [int(10) unsigned]
 * @property int     $project_id [int(11) unsigned]
 * @property int     $staff_id   [int(11) unsigned]
 * @property int     $assigned_at [int(11) unsigned]
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
                ['staff_id', 'project_id'],
                'required',
                'on' => ['admin/add', 'admin/update','admin/project/add'],
            ],
            [
                'staff_id',
                'exist',
                'targetRelation' => 'staff',
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
            'createdAtAttribute' => 'assigned_at',
            'updatedAtAttribute' => false,
        ];

        return $behaviors;
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
}
