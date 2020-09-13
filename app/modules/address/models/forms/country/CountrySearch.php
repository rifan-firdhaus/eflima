<?php namespace modules\address\models\forms\country;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\address\models\Country;
use modules\address\models\queries\CountryQuery;
use modules\core\helpers\Common;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property CountryQuery       $query
 * @property ActiveDataProvider $dataProvider
 */
class CountrySearch extends Country implements SearchableModel
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
     * @inheritdoc
     */
    public function apply($params = [], $formName = null)
    {
        $this->dataProvider->query = $query = $this->getQuery();
        $this->dataProvider->sort->defaultOrder = ['name' => SORT_ASC];

        if ($this->load($params, $formName) && $this->validate()) {
            $query->andFilterWhere(['country.is_enabled' => $this->is_enabled]);

            if (!Common::isEmpty($this->q)) {
                $query->andFilterWhere([
                    'or',
                    ['like', 'country.iso2', $this->q],
                    ['like', 'country.code', $this->q],
                    ['like', 'country.name', $this->q],
                    ['like', 'country.currency_code', $this->q],
                    ['like', 'country.phone_code', $this->q],
                    ['like', 'country.continent_code', $this->q],
                ]);
            }
        }

        return $this->dataProvider;
    }


    /**
     * @param $params
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function autoComplete($params)
    {
        $dataProvider = $this->apply($params, '');

        /** @var Select2Data $dataFactory */
        $dataFactory = Yii::createObject([
            'class' => Select2Data::class,
            'dataProvider' => $dataProvider,
            'id' => 'code',
            'label' => 'name',
            'attributes' => [
                'iso2',
            ],
        ]);

        return $dataFactory->serialize();
    }

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Country::find();

        $this->trigger(self::EVENT_QUERY);

        return $this->_query;
    }
}