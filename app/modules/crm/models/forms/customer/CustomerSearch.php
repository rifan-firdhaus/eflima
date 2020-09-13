<?php namespace modules\crm\models\forms\customer;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\helpers\Common;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\crm\models\Customer;
use modules\crm\models\queries\CustomerQuery;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use function array_keys;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property CustomerQuery      $query
 * @property ActiveDataProvider $dataProvider
 */
class CustomerSearch extends Customer implements SearchableModel
{
    use SearchableModelTrait;

    public $q;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['q'], 'string'],
            [['type'], 'in', 'range' => array_keys(Customer::types())],
            [
                'country_code',
                'exist',
                'targetRelation' => 'country',
            ],
        ];
    }

    /**
     * @param $params
     *
     * @return array
     *
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
            'label' => 'company_name',
            'attributes' => self::autoCompleteAttributes(),
        ]);

        return $dataFactory->serialize();
    }

    public static function autoCompleteAttributes()
    {
        return [
            'company_name',
            'email',
            'type',
            'contact_name' => 'primaryContact.name',
        ];
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
     * @return ActiveQuery|CustomerQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Customer::find();

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }

    /**
     * @inheritDoc
     *
     * @param CustomerQuery|ActiveQuery $query
     *
     * @return CustomerQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['customer.country_code' => $this->country_code])
            ->andFilterWhere(['customer.type' => $this->type]);

        if (!Common::isEmpty($this->q)) {
            $query->joinWith(['country', 'primaryContact']);

            $query->andFilterWhere([
                'OR',
                ['LIKE', 'customer.company_name', $this->q],
                ['LIKE', 'customer.city', $this->q],
                ['LIKE', 'customer.province', $this->q],
                ['LIKE', 'customer.email', $this->q],
                ['LIKE', 'customer.phone', $this->q],
                ['LIKE', 'customer.fax', $this->q],
                ['LIKE', 'customer.vat_number', $this->q],
                ['LIKE', 'customer.address', $this->q],
                ['LIKE', 'customer.postal_code', $this->q],
                ['LIKE', 'country_of_customer.name', $this->q],
                ['LIKE', 'country_of_customer.code', $this->q],
                ['LIKE', 'country_of_customer.iso2', $this->q],
                ['LIKE', 'primary_contact_of_customer.first_name', $this->q],
                ['LIKE', 'primary_contact_of_customer.last_name', $this->q],
                ['LIKE', 'primary_contact_of_customer.phone', $this->q],
                ['LIKE', 'primary_contact_of_customer.mobile', $this->q],
                ['LIKE', 'primary_contact_of_customer.email', $this->q],
                ['LIKE', 'primary_contact_of_customer.city', $this->q],
                ['LIKE', 'primary_contact_of_customer.province', $this->q],
                ['LIKE', 'primary_contact_of_customer.address', $this->q],
                ['LIKE', 'primary_contact_of_customer.postal_code', $this->q],
            ]);
        }

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }
}
