<?php namespace modules\finance\models\forms\proposal;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\Staff;
use modules\core\components\SearchableModelEvent;
use modules\core\helpers\Common;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\core\validators\DateValidator;
use modules\finance\models\Proposal;
use modules\finance\models\ProposalAssignee;
use modules\finance\models\ProposalStatus;
use modules\finance\models\queries\ProposalQuery;
use modules\task\models\TaskAssignee;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property ActiveQuery|ProposalQuery $query
 * @property ActiveDataProvider        $dataProvider
 */
class ProposalSearch extends Proposal implements SearchableModel
{
    use SearchableModelTrait;

    public $q;

    public $date_from;
    public $date_to;

    public $created_at_from;
    public $created_at_to;

    public $assigned_to_me;

    /** @var Staff */
    public $currentStaff;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [
                'assigned_to_me',
                'boolean',
            ],
            [
                'date_from',
                'daterange',
                'fullDay' => true,
                'type' => DateValidator::TYPE_DATE,
                'dateTo' => 'date_to',
            ],
            [
                'created_at_from',
                'daterange',
                'fullDay' => true,
                'type' => DateValidator::TYPE_DATE,
                'dateTo' => 'created_at_to',
            ],
            [
                ['status_id'],
                'each',
                'when' => function ($model) {
                    return is_array($model->status_id);
                },
                'rule' => [
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => ProposalStatus::class,
                    'targetAttribute' => ['status_id' => 'id'],
                ],
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
            [['q'], 'string'],
        ];
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
     * @return object|ActiveDataProvider
     * @throws InvalidConfigException
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
            'label' => 'title',
        ]);

        return $dataFactory->serialize();
    }

    /**
     * @inheritDoc
     *
     * @param ProposalQuery|ActiveQuery|null $query
     *
     * @return ProposalQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        // Filter by status
        $query->andFilterWhere(['proposal.status_id' => $this->status_id]);

        // Show only task assigned to the current staff
        if (isset($this->assigned_to_me) && $this->assigned_to_me) {
            $query->leftJoin(['current_proposal_assignee' => ProposalAssignee::tableName()], [
                'AND',
                "[[current_proposal_assignee.proposal_id]] = [[proposal.id]]",
                ['current_proposal_assignee.assignee_id' => $this->currentStaff->id],
            ])->andWhere(['IS NOT', 'current_proposal_assignee.id', null]);
        }

        // Filter by date range
        $query->andFilterWhere(['>=', 'proposal.date', $this->date_from])
            ->andFilterWhere(['<=', 'proposal.date', $this->date_to]);

        // Filter by created at date
        $query->andFilterWhere(['<=', 'proposal.created_at', $this->created_at_to])
            ->andFilterWhere(['>=', 'proposal.created_at', $this->created_at_from]);

        // Filter by assignee
        if (!Common::isEmpty($this->assignee_ids)) {
            $query->leftJoin(['proposal_assignee' => ProposalAssignee::tableName()], [
                'AND',
                "[[proposal_assignee.proposal_id]] = [[proposal.id]]",
                ['proposal_assignee.assignee_id' => $this->assignee_ids],
            ])->andWhere(['IS NOT', 'proposal_assignee.id', null]);
        }

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }

    /**
     * @inheritdoc
     *
     * @return ActiveQuery|ProposalQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Proposal::find();

        if (isset($this->params['status_id'])) {
            $this->_query->andWhere($this->params['status_id']);
        }

        $this->trigger(self::EVENT_QUERY, new SearchableModelEvent([
            'query' => $this->_query,
        ]));
        $relatedModelConditions = [];

        if (!empty($this->params['models'])) {
            foreach ($this->params['models'] AS $relation) {
                if ($relation instanceof Closure) {
                    $relatedModelCondition = call_user_func($relation, $this->_query);

                    if ($relatedModelCondition) {
                        $relatedModelConditions[] = $relatedModelCondition;
                    }
                } else {
                    $relatedModelCondition = ['proposal.model' => $relation['model']];

                    if (isset($relation['model_id'])) {
                        $relatedModelCondition['proposal.model_id'] = $relation['model_id'];
                    }

                    $relatedModelConditions[] = $relatedModelCondition;
                }
            }
        }

        if (!empty($this->params['model'])) {
            $relatedModelCondition = ['proposal.model' => $this->params['model']];

            if (!empty($this->params['model_id'])) {
                $relatedModelCondition['proposal.model_id'] = $this->params['model_id'];
            }

            $relatedModelConditions[] = $relatedModelCondition;
        }

        if (!empty($relatedModelConditions)) {
            array_unshift($relatedModelConditions, 'OR');

            $this->_query->andWhere($relatedModelConditions);
        }

        return $this->_query;
    }
}
