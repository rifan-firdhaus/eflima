<?php namespace modules\support\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\crm\models\CustomerContact;
use modules\crm\models\CustomerContactAccount;
use modules\support\models\queries\TicketReplyAttachmentQuery;
use modules\support\models\queries\TicketReplyQuery;
use Yii;
use yii\db\Exception as DbException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property CustomerContact                     $contact
 * @property Staff                               $staff
 * @property Ticket                              $ticket
 * @property StaffAccount|CustomerContactAccount $account
 * @property TicketReplyAttachment[]             $attachments
 * @property bool                                $isStaff
 *
 * @property int                                 $id         [int(10) unsigned]
 * @property int                                 $ticket_id  [int(11) unsigned]
 * @property int                                 $staff_id   [int(11) unsigned]
 * @property int                                 $contact_id [int(11) unsigned]
 * @property string                              $email
 * @property string                              $carbon_copy
 * @property string                              $blind_carbon_copy
 * @property string                              $name
 * @property string                              $content
 * @property int                                 $created_at [int(11) unsigned]
 */
class TicketReply extends ActiveRecord
{
    public $uploaded_attachments = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ticket_reply}}';
    }

    /**
     * @inheritdoc
     *
     * @return TicketReplyQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TicketReplyQuery(get_called_class());

        return $query->alias("ticket_reply");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'required', 'on' => ['admin/reply']],
            [
                ['blind_carbon_copy', 'carbon_copy'],
                'each',
                'rule' => ['email'],
                'on' => ['admin/reply'],
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
            'ticket_id' => Yii::t('app', 'Ticket ID'),
            'staff_id' => Yii::t('app', 'Staff ID'),
            'contact_id' => Yii::t('app', 'Contact ID'),
            'email' => Yii::t('app', 'Email'),
            'name' => Yii::t('app', 'Name'),
            'content' => Yii::t('app', 'Content'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(CustomerContact::class, ['id' => 'contact_id'])->alias('contact_of_ticket_reply');
    }

    /**
     * @return ActiveQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'staff_id'])->alias('staff_of_ticket_reply');
    }

    /**
     * @return ActiveQuery
     */
    public function getTicket()
    {
        return $this->hasOne(Ticket::class, ['id' => 'ticket_id'])->alias('ticket_of_reply');
    }

    /**
     * @return StaffAccount|CustomerContactAccount
     */
    public function getAccount()
    {
        return !empty($this->staff_id) ? $this->staff->account : $this->contact->account;
    }

    /**
     * @return ActiveQuery|TicketReplyAttachmentQuery
     */
    public function getAttachments()
    {
        return $this->hasMany(TicketReplyAttachment::class, ['reply_id' => 'id'])->alias('attachments_of_reply');
    }

    /**
     * @return bool
     */
    public function getIsStaff()
    {
        return !empty($this->staff_id);
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Save attachments
        if ($this->uploaded_attachments) {
            if (!$this->saveAttachments()) {
                throw new DbException('Failed to save Attachment');
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return bool
     */
    protected function saveAttachments()
    {
        foreach ($this->uploaded_attachments AS $attachment) {
            $model = new TicketReplyAttachment([
                'uploaded_file' => $attachment,
                'reply_id' => $this->id,
            ]);

            if (!$model->save()) {
                return false;
            }
        }

        $this->uploaded_attachments = [];

        return true;
    }

    /**
     * @inheritDoc
     */
    public function normalizeAttributes($save = false)
    {
        if ($save) {
            $this->email = $this->account->email;
            $this->name = $this->isStaff ? $this->staff->name : $this->contact->name;

            if (empty($this->created_at)) {
                $this->created_at = time();
            }

            if (is_array($this->carbon_copy)) {
                $this->carbon_copy = implode(',', $this->carbon_copy);
            }

            if (is_array($this->blind_carbon_copy)) {
                $this->blind_carbon_copy = implode(',', $this->blind_carbon_copy);
            }
        } else {
            if (is_string($this->carbon_copy)) {
                $this->carbon_copy = explode(',', $this->carbon_copy);
            }

            if (is_string($this->blind_carbon_copy)) {
                $this->blind_carbon_copy = explode(',', $this->blind_carbon_copy);
            }
        }
    }
}
