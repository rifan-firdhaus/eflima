<?php namespace modules\task\models\forms\task;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\task\models\Task;
use modules\task\models\TaskAttachment;
use modules\task\models\TaskInteractionAttachment;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Expression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskGlobalAttachment extends Model implements SearchableModel
{
    /** @var Task */
    public $task;

    public $_queries = [];

    use SearchableModelTrait;

    public function getDataProvider()
    {
        return new ActiveDataProvider(
            'query'
        );
    }

    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_queries['task'] = TaskAttachment::find()
            ->select([
                'id' => new Expression('CONCAT("task-",[[id]])'),
                'file',
                'type' => new Expression('"task"'),
            ])
            ->asArray()
            ->andWhere(['task_id' => $this->task->id]);

        $this->_queries['interaction'] = TaskInteractionAttachment::find()
            ->select([
                'id' => new Expression('CONCAT("task-interaction-",[[task_interaction_attachment.id]])'),
                'file',
                'type' => new Expression('"task-interaction"'),
            ])
            ->joinWith('interaction')
            ->asArray()
            ->andWhere(['interaction_of_attachment.task_id' => $this->task->id]);

        $this->_query = $this->_queries['task']->union($this->_queries['interaction']);


        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;

    }
}
