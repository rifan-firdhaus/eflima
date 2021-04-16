<?php namespace modules\crm\models\forms\lead;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\crm\models\Customer;
use modules\crm\models\Lead;
use modules\crm\models\LeadStatus;
use Throwable;
use Yii;
use yii\base\Model;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property-read Lead[] $models
 */
class LeadBulkSetStatus extends Model
{
    /** @var string[]|int[] */
    public $ids;

    /** @var Lead[] */
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
                'targetClass' => LeadStatus::class,
                'targetAttribute' => 'id',
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
            'status_id' => Yii::t('app', 'Status'),
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
                if (!$lead->changeStatus($this->status_id)) {
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
