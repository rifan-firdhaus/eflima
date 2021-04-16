<?php namespace modules\crm\models\forms\lead;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\Staff;
use modules\address\models\Country;
use modules\core\components\SearchableModelEvent;
use modules\core\helpers\Common;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\core\validators\DateValidator;
use modules\crm\models\Lead;
use modules\crm\models\LeadAssignee;
use modules\crm\models\LeadSource;
use modules\crm\models\LeadStatus;
use modules\crm\models\queries\LeadQuery;
use modules\task\models\TaskStatus;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property ActiveQuery|LeadQuery $query
 * @property ActiveDataProvider    $dataProvider
 */
class LeadSearch extends Lead implements SearchableModel
{
    use SearchableModelTrait;

    public $created_at_from;
    public $created_at_to;
    public $assigned_to_me;

    /** @var Staff */
    public $currentStaff;

    public $q;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        $this->dataProvider->sort->defaultOrder = ['created_at' => SORT_DESC];
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [
                ['status_id'],
                'each',
                'when' => function ($model) {
                    return is_array($model->status_id);
                },
                'rule' => [
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => LeadStatus::class,
                    'targetAttribute' => ['status_id' => 'id'],
                ],
            ],
            [
                ['source_id'],
                'each',
                'when' => function ($model) {
                    return is_array($model->source_id);
                },
                'rule' => [
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => LeadSource::class,
                    'targetAttribute' => ['source_id' => 'id'],
                ],
            ],
            [
                'created_at_from',
                'daterange',
                'fullDay' => true,
                'type' => DateValidator::TYPE_DATE,
                'dateTo' => 'created_at_to',
            ],
            [
                ['assignee_ids'],
                'each',
                'when' => function ($model) {
                    return is_array($model->assignee_ids);
                },
                'rule' => [
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => Staff::class,
                    'targetAttribute' => ['assignee_ids' => 'id'],
                ],
            ],
            [
                ['country_code'],
                'each',
                'when' => function ($model) {
                    return is_array($model->country_code);
                },
                'rule' => [
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => Country::class,
                    'targetAttribute' => ['country_code' => 'code'],
                ],
            ],
            [
                'assigned_to_me',
                'boolean'
            ],
            [
                ['q'],
                'string',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'q' => Yii::t('app', 'Keyword'),
            'assigned_to_me' => Yii::t('app', 'Show only lead assigned to me'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        unset($behaviors['attributeTypecast']);

        return $behaviors;
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
        ]);

        return $dataFactory->serialize();
    }


    /**
     * @return array
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function getStatusSummary()
    {
        $query = clone $this->getQuery();

        $statuses = $query->groupBy('lead.status_id')->joinWith('status')->select(['status_of_lead.*', 'count' => "COUNT([[lead.id]])"])->createCommand()->queryAll();
        $total = array_sum(ArrayHelper::getColumn($statuses, 'count'));
        $remainingStatuses = LeadStatus::find()->andWhere(['NOT IN', 'id', ArrayHelper::getColumn($statuses, 'id')])->asArray()->all();
        $statuses = ArrayHelper::merge($statuses, $remainingStatuses);

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
     * @inheritdoc
     *
     * @return ActiveQuery|LeadQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Lead::find();

        if (isset($this->params['status_id'])) {
            $this->_query->andWhere($this->params['status_id']);
        }

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));

        return $this->_query;
    }

    /**
     * @inheritDoc
     *
     * @param LeadQuery|ActiveQuery|null $query
     *
     * @return LeadQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        // Filter by assignee
        if (!Common::isEmpty($this->assignee_ids)) {
            $query->leftJoin(['lead_assignee' => LeadAssignee::tableName()], [
                'AND',
                "[[lead_assignee.lead_id]] = [[lead.id]]",
                ['lead_assignee.assignee_id' => $this->currentStaff->id],
            ])->andWhere(['IS NOT', 'lead_assignee.id', null]);
        }

        // Filter by status
        $query->andFilterWhere(['lead.status_id' => $this->status_id]);

        // Filter by source
        $query->andFilterWhere(['lead.source_id' => $this->source_id]);

        // Filter by country
        $query->andFilterWhere(['lead.country_code' => $this->country_code]);

        // Filter by created at date
        $query->andFilterWhere(['<=', 'task.created_at', $this->created_at_to])
            ->andFilterWhere(['>=', 'task.created_at', $this->created_at_from]);

        // Show only task assigned to current staff
        if (isset($this->assigned_to_me) && $this->assigned_to_me) {
            $query->leftJoin(['current_lead_assignee' => LeadAssignee::tableName()], [
                'AND',
                "[[current_lead_assignee.lead_id]] = [[lead.id]]",
                ['current_lead_assignee.assignee_id' => $this->assigned_to_me],
            ])->andWhere(['IS NOT', 'current_lead_assignee.id', null]);
        }

        // Filter by query string
        $query->andFilterWhere([
            'OR',
            ['LIKE', new Expression('CONCAT(lead.first_name," ",lead.last_name)'), $this->q],
            ['LIKE', 'lead.email', $this->q],
            ['LIKE', 'lead.phone', $this->q],
            ['LIKE', 'lead.mobile', $this->q],
            ['LIKE', 'lead.address', $this->q],
        ]);

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }
}
