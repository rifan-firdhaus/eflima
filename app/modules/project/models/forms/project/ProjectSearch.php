<?php namespace modules\project\models\forms\project;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\helpers\Common;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\crm\models\queries\CustomerQuery;
use modules\project\models\Project;
use modules\project\models\queries\ProjectQuery;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property ProjectQuery       $query
 * @property ActiveDataProvider $dataProvider
 */
class ProjectSearch extends Project implements SearchableModel
{
    use SearchableModelTrait;

    public $q;
    public $has_invoice = '';

    public $created_at_from;
    public $created_at_to;

    public $started_date_from;
    public $started_date_to;

    public $deadline_date_from;
    public $deadline_date_to;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['q'], 'string'],
            [['status_id', 'customer_id'], 'safe'],
        ];
    }

    /**
     * @param $params
     *
     * @return array
     *
     * @throws InvalidConfigException
     */
    public function autoComplete($params)
    {
        $this->apply($params, '');

        /** @var Select2Data $dataFactory */
        $dataFactory = Yii::createObject([
            'class' => Select2Data::class,
            'dataProvider' => $this->dataProvider,
            'id' => 'id',
            'label' => 'name',
            'attributes' => [
                'customer_name' => 'customer.name',
            ],
        ]);

        return $dataFactory->serialize();
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
     * @param ProjectQuery|ActiveQuery $query
     *
     * @return ProjectQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['project.customer_id' => $this->customer_id])
            ->andFilterWhere(['project.status_id' => $this->status_id]);

        if (!Common::isEmpty($this->has_invoice)) {
            $query->hasInvoice($this->has_invoice);
        }

        $query->andFilterWhere([
            'OR',
            ['like', 'project.name', $this->q],
            ['like', 'project.description', $this->q],
            ['like', 'customer_of_project.company_name', $this->q],
            ['like', 'CONCAT(primary_contact_of_customer.first_name," ",primary_contact_of_customer.last_name)', $this->q],
        ]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }

    /**
     * @inheritdoc
     *
     * @return ProjectQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Project::find()->joinWith([
            'customer' => function ($customerQuery) {
                /** @var CustomerQuery $customerQuery */

                return $customerQuery->joinWith('primaryContact');
            },
        ]);

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }
}