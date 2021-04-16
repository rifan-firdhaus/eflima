<?php namespace modules\task\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\file_manager\behaviors\FileUploaderBehavior;
use modules\task\models\queries\TaskInteractionAttachmentQuery;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property TaskInteraction $interaction
 *
 * @property int             $id             [int(10) unsigned]
 * @property int             $interaction_id [int(11) unsigned]
 * @property string          $file
 */
class TaskInteractionAttachment extends ActiveRecord
{
    public $uploaded_file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_interaction_attachment}}';
    }

    /**
     * @inheritdoc
     * @return TaskInteractionAttachmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TaskInteractionAttachmentQuery(get_called_class());

        return $query->alias("task_interaction_attachment");
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

        $behaviors['fileUploader'] = [
            'class' => FileUploaderBehavior::class,
            'attributes' => [
                'file' => [
                    'alias' => 'uploaded_file',
                    'base_path' => '@webroot/protected/system/task/interaction',
                    'base_url' => '@web/protected/system/task/interaction',
                ]
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
            'interaction_id' => Yii::t('app', 'Interaction ID'),
            'file' => Yii::t('app', 'File'),
        ];
    }

    /**
     * @return ActiveQuery|TaskInteraction
     */
    public function getInteraction()
    {
        return $this->hasOne(TaskInteraction::class, ['id' => 'interaction_id'])->alias('interaction_of_attachment');
    }
}
