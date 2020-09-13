<?php namespace modules\support\models\forms\ticket_reply;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\support\models\queries\TicketReplyQuery;
use modules\support\models\TicketReply;
use yii\data\ActiveDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property TicketReplyQuery   $query
 * @property ActiveDataProvider $dataProvider
 */
class TicketReplySearch extends TicketReply implements SearchableModel
{
    use SearchableModelTrait;

    public $q;

    public function rules()
    {
        return [
            [['q'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function apply($params = [], $formName = null)
    {
        $this->dataProvider->query = $query = $this->getQuery();

        $this->dataProvider->sort->defaultOrder = ['created_at' => SORT_DESC];

        if ($this->load($params, $formName) && $this->validate()) {
            $query->andFilterWhere(['like', 'ticket_department.content', $this->q]);
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

        $this->_query = TicketReply::find();

        $this->trigger(self::EVENT_QUERY);

        return $this->_query;
    }
}