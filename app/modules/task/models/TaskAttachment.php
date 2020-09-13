<?php namespace modules\task\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\file_manager\behaviors\FileUploaderBehavior;
use modules\task\models\queries\TaskAttachmentQuery;
use modules\task\models\query\TaskQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Task   $task
 *
 * @property int    $id          [int(10) unsigned]
 * @property int    $task_id     [int(11) unsigned]
 * @property string $file
 * @property int    $uploaded_at [int(11) unsigned]
 */
class TaskAttachment extends ActiveRecord
{
    public $uploaded_file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_attachment}}';
    }

    /**
     * @inheritdoc
     *
     * @return TaskAttachmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TaskAttachmentQuery(get_called_class());

        return $query->alias("task_attachment");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uploaded_file'], 'file'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestampBehaviors'] = [
            'class' => TimestampBehavior::class,
            'createdAtAttribute' => 'uploaded_at',
            'updatedAtAttribute' => false,
        ];

        $behaviors['fileUploader'] = [
            'class' => FileUploaderBehavior::class,
            'attributes' => [
                'file' => [
                    'alias' => 'uploaded_file',
                    'base_path' => '@webroot/protected/system/task/attachment',
                    'base_url' => '@web/protected/system/task/attachment',
                ],
            ],
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
            'task_id' => Yii::t('app', 'Task'),
            'file' => Yii::t('app', 'File'),
            'uploaded_at' => Yii::t('app', 'Uploaded At'),
        ];
    }

    /**
     * @return ActiveQuery|TaskQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id'])->alias('task_of_attachment');
    }
}
