<?php namespace modules\project\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\file_manager\behaviors\FileUploaderBehavior;
use modules\finance\models\queries\ExpenseAttachmentQuery;
use modules\project\models\queries\ProjectAttachmentQuery;
use modules\project\models\queries\ProjectQuery;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Project $project
 *
 * @property int     $id         [int(10) unsigned]
 * @property int     $project_id [int(11) unsigned]
 * @property string  $file
 */
class ProjectAttachment extends ActiveRecord
{
    public $uploaded_file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%project_attachment}}';
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
     *
     * @return ProjectAttachmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new ProjectAttachmentQuery(get_called_class());

        return $query->alias("project_attachment");
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
                    'base_path' => '@webroot/protected/system/project/attachment',
                    'base_url' => '@web/protected/system/project/attachment',
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
            'project_id' => Yii::t('app', 'Project ID'),
            'file' => Yii::t('app', 'File'),
        ];
    }

    /**
     * @return ActiveQuery|ProjectQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'project_id'])->alias('project_of_attachment');
    }
}
