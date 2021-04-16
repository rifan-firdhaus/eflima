<?php namespace modules\note\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\note\components\NoteRelation;
use modules\note\models\queries\NoteAttachmentQuery;
use modules\note\models\queries\NoteQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as DbException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property null|mixed             $relatedModel
 * @property null|NoteRelation      $relatedObject
 * @property NoteAttachment[]|array $attachments
 *
 * @property int                    $id         [int(10) unsigned]
 * @property string                 $model
 * @property string                 $model_id
 * @property string                 $color      [char(7)]
 * @property string                 $title
 * @property string                 $content
 * @property bool                   $is_pinned  [tinyint(1)]
 * @property bool                   $is_private [tinyint(1)]
 * @property int                    $creator_id [int(11) unsigned]
 * @property int                    $created_at [int(11) unsigned]
 * @property int                    $updater_id [int(11) unsigned]
 * @property int                    $updated_at [int(11) unsigned]
 */
class Note extends ActiveRecord
{
    protected $_relatedModel;
    public $uploaded_attachments = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%note}}';
    }

    /**
     * @inheritdoc
     *
     * @return NoteQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new NoteQuery(get_called_class());

        return $query->alias("note");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['title', 'content'],
                'string',
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                ['title'],
                'required',
                'when' => function ($model) {
                    return empty($model->content);
                },
            ],
            [
                ['content'],
                'required',
                'when' => function ($model) {
                    return empty($model->title);
                },
            ],
            [
                ['is_private'],
                'boolean',
            ],

            [
                'model',
                'in',
                'range' => array_keys(NoteRelation::map()),
            ],
            [
                'model_id',
                'validateRelatedModel',
            ],
            [
                'uploaded_attachments',
                'each',
                'rule' => [
                    'file',
                ],
            ],
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    public function validateRelatedModel()
    {
        if ($this->hasErrors() || empty($this->model)) {
            return;
        }

        $relation = NoteRelation::get($this->model);

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
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Save attachments
        if ($this->uploaded_attachments) {
            if (!$this->saveAttachments()) {
                throw new DbException('Failed to save Attachment');
            }
        }
    }

    /**
     * @return bool
     */
    protected function saveAttachments()
    {
        foreach ($this->uploaded_attachments AS $attachment) {
            $model = new NoteAttachment([
                'uploaded_file' => $attachment,
                'note_id' => $this->id,
            ]);

            if (!$model->save()) {
                return false;
            }
        }

        $this->uploaded_attachments = [];

        return true;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'creator_id' => Yii::t('app', 'Creator ID'),
            'title' => Yii::t('app', 'Title'),
            'content' => Yii::t('app', 'Content'),
            'is_private' => Yii::t('app', 'Private'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery|NoteAttachmentQuery
     */
    public function getAttachments()
    {
        return $this->hasMany(NoteAttachment::class, ['note_id' => 'id'])->alias('attachments_of_note');
    }


    /**
     * @return NoteRelation|null
     *
     * @throws InvalidConfigException
     */
    public function getRelatedObject()
    {
        if (empty($this->model)) {
            return null;
        }

        return NoteRelation::get($this->model);
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
    public function loadDefaultValues($skipIfSet = true)
    {
        if (!isset($this->is_private) || !$skipIfSet) {
            $this->is_private = true;
        }

        return parent::loadDefaultValues($skipIfSet);
    }

    /**
     * @param bool $pin
     *
     * @return bool
     */
    public function pin($pin = true)
    {
        if ($this->is_pinned === $pin) {
            return true;
        }

        $this->is_pinned = $pin;

        return $this->save(false);
    }

    /**
     * @return bool
     */
    public function unpin()
    {
        return $this->pin(false);
    }
}
