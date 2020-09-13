<?php namespace modules\support\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\file_manager\behaviors\FileUploaderBehavior;
use modules\support\models\queries\TicketReplyAttachmentQuery;
use modules\support\models\queries\TicketReplyQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property TicketReply $reply
 *
 * @property int         $id            [int(10) unsigned]
 * @property int         $reply_id      [int(11) unsigned]
 * @property string      $file
 * @property int         $uploaded_at   [int(11) unsigned]
 */
class TicketReplyAttachment extends ActiveRecord
{
    public $uploaded_file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ticket_reply_attachment}}';
    }

    /**
     * @inheritdoc
     *
     * @return TicketReplyAttachmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TicketReplyAttachmentQuery(get_called_class());

        return $query->alias("Ticket_reply_attachment");
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
                    'base_path' => '@webroot/protected/system/ticket/attachment',
                    'base_url' => '@web/protected/system/ticket/attachment',
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
            'support_id' => Yii::t('app', 'support'),
            'file' => Yii::t('app', 'File'),
            'uploaded_at' => Yii::t('app', 'Uploaded At'),
        ];
    }

    /**
     * @return ActiveQuery|TicketReplyQuery
     */
    public function getReply()
    {
        return $this->hasOne(Ticket::class, ['id' => 'reply_id'])->alias('reply_of_attachment');
    }
}
