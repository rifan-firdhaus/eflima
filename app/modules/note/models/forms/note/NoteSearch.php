<?php namespace modules\note\models\forms\note;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\note\models\Note;
use modules\note\models\queries\NoteQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property NoteQuery          $query
 * @property ActiveDataProvider $dataProvider
 */
class NoteSearch extends Note implements SearchableModel
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
            [['model_id', 'model'], 'safe'],
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
     * @param NoteQuery|ActiveQuery|null $query
     *
     * @return NoteQuery|ActiveQuery
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere([
            'OR',
            ['LIKE', 'note.title', $this->q],
            ['LIKE', 'note.content', $this->q],
        ]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }

    /**
     * @inheritdoc
     *
     * @return ActiveQuery|NoteQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Note::find();

        if (!empty($this->params['model'])) {
            $this->_query->andFilterWhere(['model' => $this->params['model']]);
        }

        if (!empty($this->params['model_id'])) {
            $this->_query->andFilterWhere(['model_id' => $this->params['model_id']]);
        }

        $this->trigger(self::EVENT_QUERY);

        return $this->_query;
    }
}
