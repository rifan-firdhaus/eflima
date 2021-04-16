<?php namespace modules\task\models\forms\task;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\account\models\Staff;
use modules\task\models\Task;
use modules\task\models\TaskPriority;
use Throwable;
use Yii;
use yii\base\Model;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskBulkSetPriority extends Model
{
    public $ids;
    public $priority_id;

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
                ['priority_id', 'ids'],
                'required',
            ],
            [
                'priority_id',
                'exist',
                'targetClass' => TaskPriority::class,
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
            'priority_id' => Yii::t('app', 'Priority'),
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

                if (!$task->changePriority($this->priority_id)) {
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
