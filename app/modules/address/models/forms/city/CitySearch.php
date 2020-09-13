<?php namespace modules\address\models\forms\city;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\address\models\City;
use modules\address\models\queries\CityQuery;
use modules\address\models\queries\ProvinceQuery;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property CityQuery          $query
 * @property ActiveDataProvider $dataProvider
 */
class CitySearch extends City implements SearchableModel
{
    use SearchableModelTrait;

    public $q;
    public $country_code;

    public function rules()
    {
        return [
            [['q', 'province_code', 'country_code'], 'string'],
            [['is_enabled'], 'boolean'],
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
        $dataProvider = $this->apply($params, '');

        /** @var Select2Data $dataFactory */
        $dataFactory = Yii::createObject([
            'class' => Select2Data::class,
            'dataProvider' => $dataProvider,
            'id' => 'id',
            'label' => 'name',
            'attributes' => [
                'province_code',
                'province_name' => 'province.name',
            ],
        ]);

        return $dataFactory->serialize();
    }

    /**
     * @inheritdoc
     */
    public function apply($params = [], $formName = null)
    {
        $this->dataProvider->query = $query = $this->getQuery();

        if ($this->load($params, $formName) && $this->validate()) {
            $query->joinWith([
                'province' => function ($provinceQuery) {
                    /** @var ProvinceQuery $provinceQuery */

                    return $provinceQuery->joinWith('country');
                },
            ]);

            $query->andFilterWhere(['city.is_enabled' => $this->is_enabled])
                ->andFilterWhere(['city.province_code' => $this->province_code])
                ->andFilterWhere(['province_of_city.country_code' => $this->country_code])
                ->andFilterWhere([
                    'OR',
                    ['like', 'city.name', $this->q],
                    ['like', 'city.code', $this->q],
                    ['like', 'province_of_city.name', $this->q],
                    ['like', 'province_of_city.code', $this->q],
                    ['like', 'country_of_province.name', $this->q],
                    ['like', 'country_of_province.code', $this->q],
                ]);
        }

        return $this->dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = City::find();

        $this->trigger(self::EVENT_QUERY);

        return $this->_query;
    }
}