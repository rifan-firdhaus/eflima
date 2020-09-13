<?php namespace modules\support\models\forms\knowledge_base;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\support\models\queries\KnowledgeBaseQuery;
use modules\support\models\KnowledgeBase;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property KnowledgeBaseQuery $query
 * @property ActiveDataProvider         $dataProvider
 */
class KnowledgeBaseSearch extends KnowledgeBase implements SearchableModel
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
     * @param KnowledgeBaseQuery|ActiveQuery|null $query
     *
     * @return KnowledgeBaseQuery|ActiveQuery
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['knowledge_base.is_enabled' => $this->is_enabled])
            ->andFilterWhere(['like', 'knowledge_base.name', $this->q]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }

    /**
     * @inheritdoc
     *
     * @return ActiveQuery|KnowledgeBaseQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = KnowledgeBase::find();

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }
}