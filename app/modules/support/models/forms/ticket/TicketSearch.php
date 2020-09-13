<?php namespace modules\support\models\forms\ticket;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\core\validators\DateValidator;
use modules\support\models\queries\TicketQuery;
use modules\support\models\Ticket;
use modules\support\models\TicketStatus;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property array              $statusSummary
 * @property TicketQuery        $query
 * @property ActiveDataProvider $dataProvider
 */
class TicketSearch extends Ticket implements SearchableModel
{
    use SearchableModelTrait;

    public $customer_id;

    public $q;
    public $created_at_from;
    public $created_at_to;

    public function rules()
    {
        return [
            [['q'], 'string'],
            [
                [
                    'department_id',
                    'created_at_to',
                    'status_id',
                    'priority_id',
                    'contact_id',
                    'customer_id',
                ],
                'safe',
            ],
            [
                'created_at_from',
                'daterange',
                'fullDay' => true,
                'type' => DateValidator::TYPE_DATE,
                'dateTo' => 'created_at_to',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'customer_id' => Yii::t('app', 'Customer'),
        ]);
    }

    /**
     * @return array
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function getStatusSummary()
    {
        $query = clone $this->getQuery();

        $statuses = $query->groupBy('ticket.status_id')->joinWith('status')->select(['status_of_ticket.*', 'count' => "COUNT([[ticket.id]])"])->createCommand()->queryAll();
        $total = array_sum(ArrayHelper::getColumn($statuses, 'count'));
        $leftStatuses = TicketStatus::find()->andWhere(['NOT IN', 'id', ArrayHelper::getColumn($statuses, 'id')])->asArray()->all();
        $statuses = ArrayHelper::merge($statuses, $leftStatuses);

        foreach ($statuses AS &$status) {
            if (!isset($status['count'])) {
                $status['count'] = 0;
            }

            $status['ratio'] = $status['count'] > 0 ? $status['count'] / $total : 0;
        }

        ArrayHelper::multisort($statuses, 'order');

        return $statuses;
    }

    /**
     * @param array $params
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function autoComplete($params = [])
    {
        $this->apply($params, '');

        /** @var Select2Data $dataFactory */
        $dataFactory = Yii::createObject([
            'class' => Select2Data::class,
            'dataProvider' => $this->dataProvider,
            'id' => 'id',
            'label' => 'subject',
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
     * @inheritDoc
     *
     * @param TicketQuery|ActiveQuery|null $query
     *
     * @return TicketQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $query->andFilterWhere(['ticket.priority_id' => $this->priority_id])
            ->andFilterWhere(['ticket.contact_id' => $this->contact_id])
            ->andFilterWhere(['contact_of_ticket.customer_id' => $this->customer_id])
            ->andFilterWhere(['ticket.status_id' => $this->status_id])
            ->andFilterWhere(['ticket.priority_id' => $this->priority_id])
            ->andFilterWhere(['ticket.department_id' => $this->department_id]);

        $query->andFilterWhere([
            'OR',
            ['like', 'ticket.subject', $this->q],
            ['like', 'ticket.content', $this->q],
            ['like', 'CONCAT(contact_of_ticket.first_name," ",contact_of_ticket.last_name)', $this->q],
        ]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }

    /**
     * @inheritdoc
     *
     * @return ActiveQuery|TicketQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Ticket::find()->joinWith('contact');

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }
}