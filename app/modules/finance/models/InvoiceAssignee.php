<?php namespace modules\finance\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\components\notification\DatabaseNotificationChannel;
use modules\account\components\notification\Notification;
use modules\account\models\Staff;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\finance\models\queries\InvoiceAssigneeQuery;
use modules\finance\models\queries\InvoiceQuery;
use modules\task\models\Task;
use modules\task\models\TaskAssignee;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 *
 * @property Invoice       $invoice
 * @property Staff $assignee
 * @property Staff $assignor
 *
 * @property int           $id          [int(10) unsigned]
 * @property int           $invoice_id  [int(11) unsigned]
 * @property int           $assignee_id [int(11) unsigned]
 * @property int           $assigned_at [int(11) unsigned]
 * @property int           $assignor_id [int(11) unsigned]
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
     * @param Staff     $assignor
     *
     * @throws InvalidConfigException
     */
    public static function sendAssignNotification($assignees, $invoice, $assignor)
    {
        if (empty($assignees)) {
            return;
        }

        if(is_numeric($assignor)){
            $assignor = Staff::find()->andWhere(['id' => $assignor])->one();
        }

        $accountIds = ArrayHelper::getColumn($assignees, 'assignee.account_id');

        $notification = new Notification([
            'to' => $accountIds,
            'title' => '{assignor} assign an invoice to you',
            'titleParams' => [
                'assignor' => $assignor->name,
            ],
            'body' => Yii::t('app','Invoice #{number} on behalf of {customer_name}'),
            'bodyParams' => [
              'number' => $invoice->number,
              'customer_name' => $invoice->customer->name
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
}
