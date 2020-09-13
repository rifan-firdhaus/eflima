<?php namespace modules\core\models\traits;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\SearchableModelEvent;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait SearchableModelTrait
{
    public $params = [];

    /** @var ActiveQuery */
    protected $_query;

    /** @var ActiveDataProvider */
    protected $_dataProvider;

    /**
     * @param ActiveQuery $query
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * @return ActiveDataProvider
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
        ]);

        return $this->_dataProvider;
    }

    /**
     * @param array       $params
     * @param null|string $formName
     *
     * @return ActiveQuery
     */
    public function apply($params = [], $formName = null)
    {
        $query = $this->getQuery();

        if ($this->load($params, $formName) && $this->validate()) {
            $this->filterQuery();
        }

        $this->trigger(self::EVENT_APPLY, new SearchableModelEvent([
            'query' => $query,
        ]));

        return $query;
    }

    /**
     * @param ActiveQuery $query
     *
     * @return ActiveQuery|null
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
     * @param string $url
     * @param array  $params
     *
     * @param bool   $toUrl
     *
     * @return string|array
     */
    public function searchUrl($url, $params = [], $toUrl = true)
    {
        if (is_array($url)) {
            $_url = array_shift($url);
            $params = ArrayHelper::merge($url, $params);
            $url = $_url;
        }

        $attributes = $this->getAttributes($this->safeAttributes());
        $modelParams = [$this->formName() => array_filter($attributes)];
        $params = ArrayHelper::merge($modelParams, $params);

        array_unshift($params, $url);

        if (!$toUrl) {
            return $params;
        }

        return Url::to($params);
    }

    /**
     * @param null|string $url
     *
     * @return string
     */
    public function clearSearchUrl($url = null)
    {
        $url = $url ? $url : Yii::$app->request->url;

        $params = [];
        parse_str(parse_url($url, PHP_URL_QUERY), $params);

        unset($params[$this->formName()]);

        return parse_url($url, PHP_URL_PATH) . (empty($params) ? '' : '?' . http_build_query($params));
    }

    /**
     * @param array $models
     */
    protected function setAssociateSort($models)
    {
        $sort = $this->dataProvider->sort;

        foreach ($models AS $type => $config) {
            $except = isset($config['except']) ? $config['except'] : [];
            $alias = isset($config['alias']) ? $config['alias'] : $type;

            if (!is_array($config)) {
                $config = ['model' => $config];
            }

            foreach ($config['model']->attributes() AS $attribute) {
                if (in_array($attribute, $except)) {
                    continue;
                }

                $sort->attributes["{$type}_{$attribute}"] = [
                    'asc' => ["{$alias}.{$attribute}" => SORT_ASC],
                    'desc' => ["{$alias}.{$attribute}" => SORT_DESC],
                    'label' => $config['model']->getAttributeLabel($attribute),
                ];
            }
        }
    }
}