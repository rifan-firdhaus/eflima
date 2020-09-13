<?php namespace modules\project\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\AccountComment;
use modules\account\models\queries\AccountCommentQuery;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\project\models\queries\ProjectDiscussionTopicQuery;
use modules\project\models\queries\ProjectQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Project                $project
 * @property AccountComment[]|array $comments
 * @property string|int             $totalComment
 *
 * @property int                    $id          [int(10) unsigned]
 * @property int                    $project_id  [int(11) unsigned]
 * @property string                 $subject
 * @property string                 $content
 * @property bool                   $is_internal [tinyint(1)]
 * @property bool                   $is_closed   [tinyint(1)]
 * @property int                    $created_at  [int(11) unsigned]
 * @property int                    $updated_at  [int(11) unsigned]
 */
class ProjectDiscussionTopic extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%project_discussion_topic}}';
    }

    /**
     * @inheritdoc
     *
     * @return ProjectDiscussionTopicQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new ProjectDiscussionTopicQuery(get_called_class());

        return $query->alias("project_discussion_topic");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subject'], 'required', 'on' => ['admin/add', 'admin/update']],
            [['is_closed', 'is_internal'], 'boolean'],
            [['content'], 'string'],
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

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'project_id' => Yii::t('app', 'Project ID'),
            'subject' => Yii::t('app', 'Subject'),
            'content' => Yii::t('app', 'Content'),
            'is_internal' => Yii::t('app', 'Is Internal'),
            'is_closed' => Yii::t('app', 'Is Closed'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery|ProjectQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'project_id'])->alias('project_of_discussion_topic');
    }

    /**
     * @return ActiveQuery|AccountCommentQuery
     */
    public function getComments()
    {
        return $this->hasMany(AccountComment::class, ['model_id' => 'id'])->andOnCondition(['account_comment.model' => 'project_discussion_topic']);
    }

    /**
     * @return int|string
     */
    public function getTotalComment()
    {
        return $this->getComments()->count('id');
    }
}
