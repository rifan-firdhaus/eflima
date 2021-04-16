<?php namespace modules\crm\models\forms\lead;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\account\models\Staff;
use modules\crm\models\Lead;
use Throwable;
use Yii;
use yii\base\Model;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class LeadBulkReassign extends Model
{
    public $ids;
    public $assignee_ids;

    /** @var Staff */
    public $staff;

    /** @var Lead[] */
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
                'allowArray' => true,
            ],
            [
                'ids',
                'exist',
                'targetClass' => Lead::class,
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
     * @return Lead[]
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

        $transaction = Lead::getDb()->beginTransaction();

        try {
            $query = Lead::find()->andWhere(['id' => $this->ids]);

            foreach ($query->each(10) AS $lead) {
                $lead->assignor_id = $this->staff->id;
                $lead->scenario = 'admin/update';

                $lead->assignee_ids = $this->assignee_ids;

                if (!$lead->save(false)) {
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
