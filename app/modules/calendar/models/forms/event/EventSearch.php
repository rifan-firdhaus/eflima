<?php namespace modules\calendar\models\forms\event;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\calendar\models\Event;
use modules\calendar\models\queries\EventQuery;
use modules\core\components\SearchableModelEvent;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property ActiveQuery|EventQuery $query
 * @property ActiveDataProvider     $dataProvider
 */
class EventSearch extends Event implements SearchableModel
{
    use SearchableModelTrait;

    public $date_start;
    public $date_end;

    public $q;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['date_end', 'date_start', 'model', 'model_id', 'q'], 'safe'],
        ];
    }

    /**
     * @param $params
     *
     * @return array
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
     * @param array  $params
     * @param string $formName
     *
     * @return array
     *
     * @throws InvalidConfigException
     */
    public function fullCalendar($params = [], $formName = '')
    {
        $results = [];

        $this->apply($params, $formName);

        $this->getQuery()->between($this->date_start, $this->date_end);

        /** @var Event[] $models */
        $models = $this->dataProvider->models;

        foreach ($models AS $model) {
            $results[] = [
                'id' => $model->id,
                'start' => date('Y-m-d H:i:s', $model->start_date),
                'end' => date('Y-m-d H:i:s', $model->end_date),
                'allDay' => false,
                'title' => $model->name,
            ];
        }

        return $results;
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
                'defaultOrder' => ['start_date' => SORT_DESC],
            ],
        ]);

        return $this->_dataProvider;
    }

    /**
     * @param EventQuery|ActiveQuery|null $query
     *
     * @return EventQuery|ActiveQuery
     *
     * @throws InvalidConfigException
     */
    public function filterQuery($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }

        $this->trigger(self::EVENT_FILTER_QUERY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }

    /**
     * @inheritDoc
     *
     * @return ActiveQuery|EventQuery
     *
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Event::find();

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
                    $relatedModelCondition = ['event.model' => $relation['model']];

                    if (isset($relation['model_id'])) {
                        $relatedModelCondition['event.model_id'] = $relation['model_id'];
                    }

                    $relatedModelConditions[] = $relatedModelCondition;
                }
            }
        }

        if (!empty($this->params['model'])) {
            $relatedModelCondition = ['event.model' => $this->params['model']];

            if (!empty($this->params['model_id'])) {
                $relatedModelCondition['event.model_id'] = $this->params['model_id'];
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