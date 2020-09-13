<?php namespace modules\crm\models\forms\customer_group;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\crm\models\CustomerGroup;
use modules\crm\models\queries\CustomerGroupQuery;
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
class CustomerGroupSearch extends CustomerGroup implements SearchableModel
{
    use SearchableModelTrait;

    public $q;

    public function rules()
    {
        return [
            [['q'], 'string'],
            [['is_enabled'], 'boolean'],
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
     * @return ActiveQuery|CustomerGroupQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = CustomerGroup::find();

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }

    /**
     * @inheritDoc
     *
     * @param CustomerGroupQuery|ActiveQuery|null $query
     *
     * @return CustomerGroupQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['customer_group.is_enabled' => $this->is_enabled])
            ->andFilterWhere([
                'OR',
                ['LIKE', 'customer_group.name', $this->q],
                ['LIKE', 'customer_group.description', $this->q],
            ]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }
}
