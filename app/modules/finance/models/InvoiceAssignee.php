<?php namespace modules\finance\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\account\components\notification\DatabaseNotificationChannel;
use modules\account\components\notification\Notification;
use modules\account\models\Staff;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\finance\models\queries\InvoiceAssigneeQuery;
use modules\finance\models\queries\InvoiceQuery;
use Throwable;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 *
 * @property Invoice $invoice
 * @property Staff   $assignee
 * @property Staff   $assignor
 *
 * @property int     $id          [int(10) unsigned]
 * @property int     $invoice_id  [int(11) unsigned]
 * @property int     $assignee_id [int(11) unsigned]
 * @property int     $assigned_at [int(11) unsigned]
 * @property int     $assignor_id [int(11) unsigned]
 */
class InvoiceAssignee extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invoice_assignee}}';
    }

    /**
     * @inheritdoc
     *
     * @return InvoiceAssigneeQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new InvoiceAssigneeQuery(get_called_class());

        return $query->alias("invoice_assignee");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['admin/add'] = $scenarios['default'];
        $scenarios['admin/update'] = $scenarios['admin/add'];
        $scenarios['admin/invoice/add'] = $scenarios['admin/add'];

        return $scenarios;
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'invoice_id' => Yii::t('app', 'Invoice'),
            'assignee_id' => Yii::t('app', 'Assignee'),
            'assigned_at' => Yii::t('app', 'Assigned At'),
            'assignor_id' => Yii::t('app', 'Assignor'),
        ];
    }

    /**
     * @return ActiveQuery|InvoiceQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id'])->alias('invoice_of_assignee');
    }

    /**
     * @return ActiveQuery
     */
    public function getAssignee()
    {
        return $this->hasOne(Staff::class, ['id' => 'assignee_id'])->alias('profile_of_assignee');
    }

    /**
     * @return ActiveQuery
     */
    public function getAssignor()
    {
        return $this->hasOne(Staff::class, ['id' => 'assignor_id'])->alias('assignor_of_assignee');
    }

    /**
     * @param InvoiceAssignee[] $assignees
     * @param Invoice           $invoice
     * @param Staff             $assignor
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public static function sendAssignNotification($assignees, $invoice, $assignor)
    {
        if (!$assignor instanceof Staff) {
            $assignor = Staff::find()->andWhere(['id' => $assignor])->one();

            if(!$assignor){
                throw new Exception('Invalid assignor');
            }
        }

        $assignees = array_filter($assignees, function ($assignee) {
            return $assignee->assignee_id != $assignee->assignor_id;
        });

        if (empty($assignees)) {
            return;
        }

        $accountIds = ArrayHelper::getColumn($assignees, 'assignee.account_id');

        $notification = new Notification([
            'to' => $accountIds,
            'title' => '{assignor} assign an invoice to you',
            'titleParams' => [
                'assignor' => $assignor->name,
            ],
            'body' => Yii::t('app', 'Invoice #{number} on behalf of {customer_name}'),
            'bodyParams' => [
                'number' => $invoice->number,
                'customer_name' => $invoice->customer->name,
            ],
            'channels' => [
                DatabaseNotificationChannel::class => [
                    'url' => ['/finance/admin/invoice/view', 'id' => $invoice->id],
                    'is_internal_url' => true,
                ],
            ],
        ]);

        $notification->send();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && in_array($this->scenario, ['admin/invoice/add', 'admin/add'])) {
            $this->recordAssignedHistory();
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $this->recordRemoveAssignementHistory();
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function recordRemoveAssignementHistory()
    {
        return Account::history()->save('invoice_assignee.delete', [
            'params' => $this->getHistoryParams(),
            'description' => 'Removing assignment of {assignee_name} from lead "{invoice_number}"',
            'tag' => 'release_assignment',
            'model' => Invoice::class,
            'model_id' => $this->invoice_id,
        ]);
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function recordAssignedHistory()
    {
        return Account::history()->save('invoice_assignee.add', [
            'params' => $this->getHistoryParams(),
            'description' => 'Assigning {assignee_name} to lead "{invoice_number}"',
            'tag' => 'assign',
            'model' => Invoice::class,
            'model_id' => $this->invoice_id,
        ]);
    }

    /**
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['assignee_id', 'assignor_id', 'invoice_id']);

        return array_merge($params, [
            'assignee_name' => $this->assignee->name,
            'assignor_name' => $this->assignor->name,
            'invoice_number' => $this->invoice->number,
        ]);
    }

    /**
     * @param Event $event
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public static function deleteAllRelatedToDeletedStaff($event)
    {
        /** @var Staff $staff */
        $staff = $event->sender;

        $models = InvoiceAssignee::find()
            ->andWhere([
                'OR',
                ['assignor_id' => $staff->id],
                ['assignee_id' => $staff->id],
            ])
            ->all();

        foreach($models AS $model){
            if(!$model->delete()){
                throw new Exception('Failed to delete related assignee');
            }
        }
    }
}
