<?php namespace modules\crm\models\forms\lead_follow_up;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\crm\models\Lead;
use modules\crm\models\LeadFollowUp;
use modules\crm\models\queries\LeadFollowUpQuery;
use modules\crm\models\queries\LeadSourceQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property LeadSourceQuery    $query
 * @property ActiveDataProvider $dataProvider
 */
class LeadFollowUpSearch extends LeadFollowUp implements SearchableModel
{
    use SearchableModelTrait;

    /** @var Lead */
    public $currentLead;

    public $q;

    /**
     * @inheritDoc
     */
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
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
        ]);

        return $this->_dataProvider;
    }

    /**
     * @inheritdoc
     *
     * @return ActiveQuery|LeadFollowUpQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = LeadFollowUp::find();

        if (isset($this->currentLead)) {
            $this->_query->andWhere(['lead_follow_up.lead_id' => $this->currentLead->id]);
        }

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }

    /**
     * @inheritDoc
     *
     * @param LeadFollowUpQuery|ActiveQuery|null $query
     *
     * @return LeadFollowUpQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['LIKE', 'lead_follow_up.description', $this->q]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }
}
