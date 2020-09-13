<?php namespace modules\content\models\forms\post;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\content\models\Post;
use modules\core\db\ActiveQuery;
use modules\core\models\interfaces\SearchableModel;
use modules\core\models\traits\SearchableModelTrait;
use modules\ui\widgets\inputs\Select2Data;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property ActiveQuery        $query
 * @property ActiveDataProvider $dataProvider
 */
class PostSearch extends Post implements SearchableModel
{
    use SearchableModelTrait;

    public $q;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['q','type_id'], 'safe'],
            [['is_published'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'q' => Yii::t('app', 'Search Query'),
        ]);
    }

    /**
     * @param $params
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function autoComplete($params)
    {
        $dataProvider = $this->apply($params, '');

        /** @var Select2Data $dataFactory */
        $dataFactory = Yii::createObject([
            'class' => Select2Data::class,
            'dataProvider' => $dataProvider,
            'id' => 'id',
            'label' => 'title',
        ]);

        return $dataFactory->serialize();
    }

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = Post::find();

        $this->trigger(self::EVENT_QUERY);

        return $this->_query;
    }

    /**
     * @inheritdoc
     */
    public function apply($params = [], $formName = null)
    {
        $this->dataProvider->query = $query = $this->getQuery();

        if ($this->load($params, $formName) && $this->validate()) {
            $query->andFilterWhere(['post.is_published' => $this->is_published])
                ->andFilterWhere(['post.type_id' => $this->type_id]);

            $query->andFilterWhere([
                'OR',
                ['LIKE', 'post.title', $this->q],
                ['LIKE', 'post.content', $this->q],
            ]);
        }

        $this->trigger(self::EVENT_APPLY);

        return $this->dataProvider;
    }
}