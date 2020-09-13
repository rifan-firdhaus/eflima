<?php namespace modules\task\models\forms\task_interaction;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\queries\StaffQuery;
use modules\core\components\SearchableModelEvent;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\task\models\query\TaskInteractionQuery;
use modules\task\models\TaskInteraction;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property TaskInteractionQuery $query
 * @property ActiveDataProvider   $dataProvider
 */
class TaskInteractionSearch extends TaskInteraction implements SearchableModel
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
                'defaultOrder' => ['at' => SORT_DESC],
            ],
        ]);

        return $this->_dataProvider;
    }

    /**
     * @inheritDoc
     *
     * @param ActiveQuery|TaskInteractionQuery
     *
     * @return ActiveQuery|TaskInteractionQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['like', 'task_interaction.label', $this->q]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }

    /**
     * @inheritdoc
     *
     * @return ActiveQuery|TaskInteractionQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = TaskInteraction::find()->with([
            'staff' => function ($query) {
                /** @var StaffQuery $query */
                return $query->with('account');
            },
        ]);

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }
}