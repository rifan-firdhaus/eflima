<?php namespace modules\crm\models\forms\customer_contact;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\crm\models\Customer;
use modules\crm\models\CustomerContact;
use modules\crm\models\queries\CustomerContactQuery;
use modules\crm\models\queries\CustomerGroupQuery;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property CustomerGroupQuery $query
 * @property ActiveDataProvider $dataProvider
 */
class CustomerContactSearch extends CustomerContact implements SearchableModel
{
    use SearchableModelTrait;

    /** @var Customer */
    public $currentCustomer;

    public $q;

    public function rules()
    {
        return [
            [['q'], 'string'],
        ];
    }

    /**
     * @param $params
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function autoComplete($params)
    {
        $this->apply($params, '');

        /** @var Select2Data $dataFactory */
        $dataFactory = Yii::createObject([
            'class' => Select2Data::class,
            'dataProvider' => $this->dataProvider,
            'id' => 'id',
            'label' => 'name',
            'attributes' => [
                'customer_company_name' => 'customer.company_name',
                'avatar' => function ($model) {
                    /** @var CustomerContact $model */

                    return $model->account->getFileVersionUrl('avatar', 'thumbnail');
                },
                'email' => 'account.email',
                'customer_type' => 'customer.type',
            ],
        ]);

        return $dataFactory->serialize();
    }

    /**
     * @inheritDoc
     */
    public function getDataProvider()
    {
        if (isset($this->_dataProvider)) {
            return $this->_dataProvider;
        }

        $this->_dataProvider = Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $this->getQuery(),
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
        ]);

        return $this->_dataProvider;
    }

    /**
     * @inheritdoc
     *
     * @return ActiveQuery|CustomerContactQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = CustomerContact::find();

        if (isset($this->currentCustomer)) {
            $this->_query->andWhere(['customer_contact.customer_id' => $this->currentCustomer->id]);
        }

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }

    /**
     * @inheritDoc
     *
     * @param CustomerContactQuery|ActiveQuery|null $query
     *
     * @return CustomerContactQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere([
            'OR',
            ['LIKE', 'CONCAT(customer_contact.first_name," ",customer_contact.last_name)', $this->q],
            ['LIKE', 'customer_contact.address', $this->q],
            ['LIKE', 'customer_contact.phone', $this->q],
            ['LIKE', 'customer_contact.email', $this->q],
            ['LIKE', 'customer_contact.postal_code', $this->q],
        ]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }
}
