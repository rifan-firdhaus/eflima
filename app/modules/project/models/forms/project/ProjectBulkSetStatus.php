<?php namespace modules\project\models\forms\project;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\crm\models\Customer;
use modules\crm\models\Lead;
use modules\crm\models\LeadStatus;
use modules\project\models\Project;
use modules\project\models\ProjectStatus;
use Throwable;
use Yii;
use yii\base\Model;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property-read Project[] $models
 */
class ProjectBulkSetStatus extends Model
{
    /** @var string[]|int[] */
    public $ids;

    /** @var Project[] */
    public $_models;

    /** @var string|int */
    public $status_id;

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
                'targetClass' => ProjectStatus::class,
                'targetAttribute' => 'id',
            ],
            [
                'ids',
                'exist',
                'targetClass' => Project::class,
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
            'status_id' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return Project[]
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

        $transaction = Project::getDb()->beginTransaction();

        try {
            $query = Project::find()->andWhere(['id' => $this->ids]);

            foreach ($query->each(10) AS $project) {
                if (!$project->changeStatus($this->status_id)) {
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
