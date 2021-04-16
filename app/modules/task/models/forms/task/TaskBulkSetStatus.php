<?php namespace modules\task\models\forms\task;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\account\models\Staff;
use modules\crm\models\Customer;
use modules\crm\models\CustomerGroup;
use modules\task\models\Task;
use modules\task\models\TaskStatus;
use Throwable;
use Yii;
use yii\base\Model;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskBulkSetStatus extends Model
{
    public $ids;
    public $status_id;

    /** @var Staff */
    public $staff;

    /** @var Task[] */
    protected $_models;


    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [
                ['status_id', 'ids'],
                'required',
            ],
            [
                'status_id',
                'exist',
                'targetClass' => TaskStatus::class,
                'targetAttribute' => 'id',
            ],
            [
                'ids',
                'exist',
                'targetClass' => Task::class,
                'targetAttribute' => 'id',
                'allowArray' => true,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'status_id' => Yii::t('app', 'Status')
        ];
    }

    /**
     * @return Task[]
     */
    public function getModels(){
        return $this->_models;
    }

    /**
     * @return bool
     *
     * @throws Throwable
     */
    public function save()
    {
        if(!$this->validate()){
            return false;
        }

        $transaction = Task::getDb()->beginTransaction();

        try {
            $query = Task::find()->andWhere(['id' => $this->ids]);

            foreach ($query->each(10) AS $task) {
                $task->staff = $this->staff;

                if (!$task->changeStatus($this->status_id)) {
                    $transaction->rollBack();

                    return false;
                }
            }

            $transaction->commit();
        } catch (Exception $exception) {
            $transaction->rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }
}
