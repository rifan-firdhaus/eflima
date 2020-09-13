<?php namespace modules\address\models\forms\province;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\address\models\Province;
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
 * @property ProvinceQuery      $query
 * @property ActiveDataProvider $dataProvider
 */
class ProvinceSearch extends Province implements SearchableModel
{
    use SearchableModelTrait;

    public $q;

    public function rules()
    {
        return [
            [['q', 'country_code'], 'string'],
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
            'id' => 'code',
            'label' => 'name',
            'attributes' => [
                'country_code',
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
            $query->joinWith('country')
                ->andFilterWhere(['province.is_enabled' => $this->is_enabled])
                ->andFilterWhere(['province.country_code' => $this->country_code])
                ->andFilterWhere([
                    'OR',
                    ['like', 'province.name', $this->q],
                    ['like', 'province.code', $this->q],
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

        $this->_query = Province::find();

        $this->trigger(self::EVENT_QUERY);

        return $this->_query;
    }
}