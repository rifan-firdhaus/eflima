<?php namespace modules\account\models\forms\history;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\History;
use modules\core\components\SearchableModelEvent;
use modules\core\db\ActiveQuery;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property ActiveQuery        $query
 * @property ActiveDataProvider $dataProvider
 */
class HistorySearch extends History implements SearchableModel
{
    use SearchableModelTrait;

    public $q;

    public function init()
    {
        parent::init();

        $this->dataProvider->sort->defaultOrder = ['at' => SORT_DESC];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['q'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'q' => Yii::t('app', 'Search Query'),
            'is_blocked' => Yii::t('app', 'Block'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = History::find();

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
                    $relatedModelCondition = ['history.model' => $relation['model']];

                    if (isset($relation['model_id'])) {
                        $relatedModelCondition['history.model_id'] = $relation['model_id'];
                    }

                    $relatedModelConditions[] = $relatedModelCondition;
                }
            }
        }

        if (!empty($this->params['model'])) {
            $relatedModelCondition = ['history.model' => $this->params['model']];

            if (!empty($this->params['model_id'])) {
                $relatedModelCondition['history.model_id'] = $this->params['model_id'];
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
