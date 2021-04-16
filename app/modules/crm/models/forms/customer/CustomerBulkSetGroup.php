<?php namespace modules\crm\models\forms\customer;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\crm\models\Customer;
use modules\crm\models\CustomerGroup;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property-read Customer[] $models
 */
class CustomerBulkSetGroup extends Model
{
    /** @var string[]|int[] */
    public $id;

    /** @var Customer[] */
    public $_models;

    /** @var string|int */
    public $group_id;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [
                ['group_id', 'id'],
                'required',
            ],
            [
                'group_id',
                'exist',
                'targetClass' => CustomerGroup::class,
                'targetAttribute' => 'id',
            ],
            [
                'id',
                'exist',
                'targetClass' => Customer::class,
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
            'group_id' => Yii::t('app', 'Group')
        ];
    }

    /**
     * @return Customer[]
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

        $transaction = Customer::getDb()->beginTransaction();

        try {
            $query = Customer::find()->andWhere(['id' => $this->id]);

            foreach ($query->each(10) AS $customer) {
                $customer->group_id = $this->group_id;

                if (!$customer->save(false)) {
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
