<?php

namespace modules\task\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\account\Account;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\task\models\query\TaskChecklistQuery;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use function array_key_exists;
use function in_array;
use function strpos;
use function time;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Staff  $checker
 * @property Task   $task
 * @property int    $id         [int(10) unsigned]
 * @property int    $task_id    [int(11) unsigned]
 * @property string $label
 * @property bool   $is_checked [tinyint(1) unsigned]
 * @property int    $checked_at [int(11) unsigned]
 * @property int    $checker_id [int(11) unsigned]
 * @property int    $created_at [int(11) unsigned]
 * @property int    $updated_at [int(11) unsigned]
 */
class TaskChecklist extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_checklist}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'label'], 'required'],
            [['is_checked'], 'boolean'],
            [['order'], 'integer', 'skipOnEmpty' => true],
            [
                ['task_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Task::class,
                'targetAttribute' => ['task_id' => 'id'],
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
            'task_id' => Yii::t('app', 'Task ID'),
            'label' => Yii::t('app', 'Label'),
            'is_checked' => Yii::t('app', 'Is Checked'),
            'checked_at' => Yii::t('app', 'Checked At'),
            'checker_id' => Yii::t('app', 'Checker ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['admin/add'] = $scenarios['default'];
        $scenarios['admin/update'] = $scenarios['admin/add'];
        $scenarios['admin/task/add'] = $scenarios['admin/add'];
        $scenarios['admin/task/update'] = $scenarios['admin/update'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        $transactions = parent::transactions();

        $transactions['default'] = self::OP_ALL;
        $transactions['admin/add'] = self::OP_ALL;
        $transactions['admin/update'] = self::OP_ALL;

        return $transactions;
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

        $behaviors['attributeTypecast'] = [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'is_checked' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                'order' => AttributeTypecastBehavior::TYPE_INTEGER,
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (
            $this->isAttributeChanged('is_checked') &&
            $this->is_checked != $this->getOldAttribute('is_checked')
        ) {
            $this->checked_at = time();

            if (strpos('admin/', $this->scenario) === 0) {
                /** @var StaffAccount $account */
                $account = Yii::$app->user->identity;

                $this->checker_id = $account->profile->id;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (in_array($this->scenario, ['admin/add', 'admin/update', 'admin/task/add', 'admin/task/update'])) {
            if (
                array_key_exists('is_checked', $changedAttributes) &&
                $changedAttributes['is_checked'] != $this->is_checked
            ) {
                $this->recordCheckedHistory();
            } elseif (!empty($changedAttributes)) {
                $this->recordSavedHistory();
            }
        }

        if (in_array($this->scenario, ['admin/add', 'admin/update']) && $this->task->progress_calculation === Task::PROGRESS_CALCULATION_CHECKLIST) {
            $this->task->calculateProgress();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        if (!isset($this->order) || !$skipIfSet) {
            $this->order = 9999;
        }

        return parent::loadDefaultValues($skipIfSet);
    }

    /**
     * @return ActiveQuery
     */
    public function getChecker()
    {
        return $this->hasOne(Staff::class, ['id' => 'checker_id'])->alias('checker_of_checklist');
    }

    /**
     * @return ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id'])->alias('task_of_checklist');
    }

    /**
     * @inheritdoc
     * @return TaskChecklistQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new TaskChecklistQuery(get_called_class());

        return $query->alias("task_checklist");
    }

    /**
     * @param string|int           $taskId
     * @param array|int[]|string[] $sort
     *
     * @return bool
     * @throws Exception
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public static function sort($taskId, $sort = [])
    {
        $models = self::find()->andWhere(['task_id' => $taskId, 'id' => $sort])->indexBy('id')->all();

        $transaction = self::getDb()->beginTransaction();

        try {
            foreach ($sort AS $order => $taskChecklistId) {
                if (!isset($models[$taskChecklistId])) {
                    continue;
                }

                $model = $models[$taskChecklistId];

                $model->order = $order;

                if (!$model->save(false)) {
                    $transaction->rollBack();

                    return false;
                }
            }
        } catch (\Exception $e) {
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
     * @return array
     */
    public function getHistoryParams()
    {
        $params = $this->getAttributes(['id', 'task_id', 'label', 'is_checked']);
        $params['task_title'] = $this->task->title;

        return $params;
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    protected function recordCheckedHistory()
    {
        $historyAction = $this->is_checked ? 'Checking' : 'Unchecking';
        $historyEvent = $this->is_checked ? 'check' : 'uncheck';

        $historyRelationship = [
            Task::class => $this->task_id,
            TaskChecklist::class => $this->id,
        ];

        if (!empty($this->task->model)) {
            $historyRelationship[get_class($this->task->getRelatedModel())] = $this->task->model_id;
        }

        return Account::history()->save("task_checklist.{$historyEvent}", [
            'tag' => 'update',
            'description' => "{$historyAction} checklist \"{label}\" of task \"{task_title}\"",
            'params' => $this->getHistoryParams(),
            'model' => Task::class,
            'model_id' => $this->task_id,
        ]);
    }

    /**
     * @return bool
     * @throws Exception
     * @throws Throwable
     */
    public function recordSavedHistory()
    {
        $history = [
            'params' => $this->getHistoryParams(),
            'model' => Task::class,
            'model_id' => $this->task_id,
        ];

        if (in_array($this->scenario, ['admin/task/add', 'admin/add'])) {
            $history['description'] = 'Adding checklist "{label}" to task "{task_title}"';
        } else {
            $history['description'] = 'Updating checklist "{label}" of task "{task_title}"';
        }

        $event = $this->scenario === 'admin/add' ? 'task_checklist.add' : 'task_checklist.update';
        $history['tag'] = $this->scenario === 'admin/add' ? 'add' : 'update';

        return Account::history()->save($event, $history);
    }

}
