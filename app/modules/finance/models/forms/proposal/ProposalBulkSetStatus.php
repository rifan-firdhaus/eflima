<?php namespace modules\finance\models\forms\proposal;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\finance\models\Proposal;
use modules\finance\models\ProposalStatus;
use Throwable;
use Yii;
use yii\base\Model;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property-read Proposal[] $models
 */
class ProposalBulkSetStatus extends Model
{
    /** @var string[]|int[] */
    public $ids;

    /** @var Proposal[] */
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
                'targetClass' => ProposalStatus::class,
                'targetAttribute' => 'id',
            ],
            [
                'ids',
                'exist',
                'targetClass' => Proposal::class,
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
     * @return Proposal[]
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

        $transaction = Proposal::getDb()->beginTransaction();

        try {
            $query = Proposal::find()->andWhere(['id' => $this->ids]);

            foreach ($query->each(10) AS $proposal) {
                if (!$proposal->changeStatus($this->status_id)) {
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
