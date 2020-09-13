<?php namespace modules\account\models\forms\account_comment;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\AccountComment;
use modules\account\models\queries\AccountCommentQuery;
use modules\core\components\SearchableModelEvent;
use modules\core\db\ActiveQuery;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property ActiveDataProvider              $dataProvider
 * @property AccountCommentQuery|ActiveQuery $query
 */
class AccountCommentSearch extends AccountComment implements SearchableModel
{
    use SearchableModelTrait;

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
                'defaultOrder' => ['posted_at' => SORT_DESC],
            ],
        ]);

        return $this->_dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = AccountComment::find();

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }
}