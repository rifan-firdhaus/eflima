<?php namespace modules\task\models\forms\task;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\account\models\Staff;
use modules\task\models\Task;
use Throwable;
use Yii;
use yii\base\Model;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskBulkReassign extends Model
{
    public $ids;
    public $assignee_ids;

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
                ['assignee_ids', 'ids'],
                'required',
            ],
            [
                'assignee_ids',
                'exist',
                'targetClass' => Staff::class,
                'targetAttribute' => 'id',
                'allowArray' => true
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
            'assignee_ids' => Yii::t('app', 'Assignee'),
        ];
    }

    /**
     * @return Task[]
     */
    public function getModels()
    {
        return $this->_models;
    }

    /**
     * @return bool
     *
     * @throws Throwable
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Task::getDb()->beginTransaction();

        try {
            $query = Task::find()->andWhere(['id' => $this->ids]);

            foreach ($query->each(10) AS $task) {
                $task->staff = $this->staff;
                $task->assignor_id = $this->staff->id;
                $task->scenario = 'admin/update';

                $task->assignee_ids = $this->assignee_ids;

                if (!$task->save(false)) {
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
