<?php namespace modules\support\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use Exception;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\crm\models\Customer;
use modules\crm\models\CustomerContact;
use modules\crm\models\queries\CustomerQuery;
use modules\support\models\queries\TicketAttachmentQuery;
use modules\support\models\queries\TicketQuery;
use modules\task\models\Task;
use modules\task\models\TaskPriority;
use modules\task\models\TaskStatus;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as DbException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property CustomerContact    $contact
 * @property TicketDepartment   $department
 * @property TicketPriority     $priority
 * @property TicketStatus       $status
 * @property TicketAttachment[] $attachments
 * @property Customer           $customer
 *
 * @property int                $id            [int(10) unsigned]
 * @property int                $priority_id   [int(11) unsigned]
 * @property int                $status_id     [int(11) unsigned]
 * @property int                $contact_id    [int(11) unsigned]
 * @property int                $department_id [int(11) unsigned]
 * @property string             $subject
 * @property string             $content
 * @property string             $email
 * @property string             $name
 * @property string             $carbon_copy
 * @property string             $blind_carbon_copy
 * @property int                $created_at    [int(11) unsigned]
 * @property int                $updated_at    [int(11) unsigned]
 * @property int                $project_id    [int(11) unsigned]
 */
class Ticket extends ActiveRecord
{
    public $uploaded_attachments = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ticket}}';
    }

    /**
     * @inheritdoc
     *
     * @return TicketQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TicketQuery(get_called_class());

        return $query->alias("ticket");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['content', 'subject', 'contact_id'],
                'required',
                'on' => ['admin/add', 'admin/update'],
            ],
            [
                'priority_id',
                'exist',
                'targetRelation' => 'priority',
            ],
            [
                'status_id',
                'exist',
                'targetRelation' => 'status',
            ],
            [
                'department_id',
                'exist',
                'targetRelation' => 'department',
            ],
            [
                'contact_id',
                'exist',
                'targetRelation' => 'contact',
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
            'priority_id' => Yii::t('app', 'Priority'),
            'status_id' => Yii::t('app', 'Status'),
            'contact_id' => Yii::t('app', 'Contact'),
            'department_id' => Yii::t('app', 'Department'),
            'uploaded_attachments' => Yii::t('app', 'Attachments'),
            'subject' => Yii::t('app', 'Subject'),
            'content' => Yii::t('app', 'Content'),
            'email' => Yii::t('app', 'Email'),
            'name' => Yii::t('app', 'Name'),
            'carbon_copy' => Yii::t('app', 'Carbon Copy'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(CustomerContact::class, ['id' => 'contact_id'])->alias('contact_of_ticket');
    }

    /**
     * @return ActiveQuery
     */
    public function getDepartment()
    {
        return $this->hasOne(TicketDepartment::class, ['id' => 'department_id'])->alias('department_of_ticket');
    }

    /**
     * @return ActiveQuery
     */
    public function getPriority()
    {
        return $this->hasOne(TicketPriority::class, ['id' => 'priority_id'])->alias('priority_of_ticket');
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(TicketStatus::class, ['id' => 'status_id'])->alias('status_of_ticket');
    }

    /**
     * @return ActiveQuery|TicketAttachmentQuery
     */
    public function getAttachments()
    {
        return $this->hasMany(TicketAttachment::class, ['ticket_id' => 'id'])->alias('attachments_of_ticket');
    }

    /**
     * @return ActiveQuery|CustomerQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id'])->via('contact')->alias('customer_of_ticket');
    }

    /**
     * @inheritDoc
     */
    public function normalizeAttributes($save = false)
    {
        if ($save) {
            $this->email = $this->contact->email;
            $this->name = $this->contact->name;
        }
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
            $model = new TicketAttachment([
                'uploaded_file' => $attachment,
                'ticket_id' => $this->id,
            ]);

            if (!$model->save()) {
                return false;
            }
        }

        $this->uploaded_attachments = [];

        return true;
    }


    /**
     * @param integer $statusId
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function changeStatus($statusId)
    {
        if (!TicketPriority::find()->andWhere(['id' => $statusId])->enabled()->exists()) {
            $this->addError('status_id', Yii::t('app', '{object} doesn\'t exists', [
                'object' => Yii::t('app', 'Status'),
            ]));

            return false;
        }

        if ($this->status_id == $statusId) {
            return true;
        }

        $this->status_id = $statusId;

        if (!$this->save(false)) {
            return false;
        }

        return true;
    }

    /**
     * @param int|string     $statusId
     * @param int[]|string[] $taskIds
     *
     * @return bool
     *
     * @throws DbException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function changeStatuses($statusId, $taskIds)
    {
        if (is_string($taskIds)) {
            $taskIds = explode(',', $taskIds);
        }

        if (empty($taskIds)) {
            return true;
        }

        $tickets = Ticket::find()->andWhere(['id' => $taskIds])
            ->andWhere(['!=', 'status_id', $statusId])
            ->all();

        $transaction = self::getDb()->beginTransaction();

        try {
            foreach ($tickets AS $ticket) {
                if ($ticket->changeStatus($statusId)) {
                    continue;
                }

                $transaction->rollBack();

                return false;
            }
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        $transaction->commit();

        return true;
    }

    /**
     * @param integer $priorityId
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function changePriority($priorityId)
    {
        if (!TicketPriority::find()->andWhere(['id' => $priorityId])->enabled()->exists()) {
            $this->addError('status_id', Yii::t('app', '{object} doesn\'t exists', [
                'object' => Yii::t('app', 'Priority'),
            ]));

            return false;
        }

        if ($this->priority_id == $priorityId) {
            return true;
        }

        $this->priority_id = $priorityId;

        return $this->save(false);
    }


    /**
     * @param int|string     $priorityId
     * @param int[]|string[] $taskIds
     *
     * @return bool
     *
     * @throws DbException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function changePriorities($priorityId, $taskIds)
    {
        if (is_string($taskIds)) {
            $taskIds = explode(',', $taskIds);
        }

        if (empty($taskIds)) {
            return true;
        }

        $tickets = Ticket::find()->andWhere(['id' => $taskIds])
            ->andWhere(['!=', 'priority_id', $priorityId])
            ->all();

        $transaction = self::getDb()->beginTransaction();

        try {
            foreach ($tickets AS $ticket) {
                if ($ticket->changePriority($priorityId)) {
                    continue;
                }

                $transaction->rollBack();

                return false;
            }
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        $transaction->commit();

        return true;
    }

}
