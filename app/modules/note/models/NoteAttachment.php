<?php namespace modules\note\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\file_manager\behaviors\FileUploaderBehavior;
use modules\note\models\queries\NoteAttachmentQuery;
use modules\note\models\queries\NoteQuery;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Note   $note
 *
 * @property int    $id         [int(10) unsigned]
 * @property int    $note_id    [int(11) unsigned]
 * @property string $file
 * @property int    $uploaded_at [int(11) unsigned]
 */
class NoteAttachment extends ActiveRecord
{
    public $uploaded_file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%note_attachment}}';
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
     * @return NoteAttachmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new NoteAttachmentQuery(get_called_class());

        return $query->alias("note_attachment");
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
                    'base_path' => '@webroot/protected/system/note/attachment',
                    'base_url' => '@web/protected/system/note/attachment',
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
            'note_id' => Yii::t('app', 'Note ID'),
            'file' => Yii::t('app', 'File'),
        ];
    }

    /**
     * @return ActiveQuery|NoteQuery
     */
    public function getNote()
    {
        return $this->hasOne(Note::class, ['id' => 'note_id'])->alias('note_of_attachment');
    }
}
