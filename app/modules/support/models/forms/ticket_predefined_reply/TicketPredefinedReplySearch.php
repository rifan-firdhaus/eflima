<?php namespace modules\support\models\forms\ticket_predefined_reply;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\support\models\queries\TicketPredefinedReplyQuery;
use modules\support\models\TicketPredefinedReply;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property TicketPredefinedReplyQuery $query
 * @property ActiveDataProvider         $dataProvider
 */
class TicketPredefinedReplySearch extends TicketPredefinedReply implements SearchableModel
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
     * @inheritDoc
     *
     * @param TicketPredefinedReplyQuery|ActiveQuery|null $query
     *
     * @return TicketPredefinedReplyQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['ticket_predefined_reply.is_enabled' => $this->is_enabled])
            ->andFilterWhere(['like', 'ticket_predefined_reply.title', $this->q]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }

    /**
     * @inheritdoc
     *
     * @return ActiveQuery|TicketPredefinedReplyQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = TicketPredefinedReply::find();

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }
}