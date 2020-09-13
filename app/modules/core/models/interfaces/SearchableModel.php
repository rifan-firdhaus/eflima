<?php namespace modules\core\models\interfaces;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\data\ActiveDataProvider;
use yii\data\BaseDataProvider;
use yii\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
interface SearchableModel
{
    const EVENT_QUERY = 'eventQuery';
    const EVENT_APPLY = 'eventApply';
    const EVENT_FILTER_QUERY = 'eventFilterQueyr';

    /**
     * @param array       $params
     * @param null|string $formName
     *
     * @return BaseDataProvider
     */
    public function apply($params = [], $formName = null);

    /**
     * @param ActiveQuery|null $query
     *
     * @return ActiveQuery
     */
    public function filterQuery($query = null);

    /**
     * @return ActiveQuery
     */
    public function getQuery();

    /**
     * @param ActiveQuery $query
     */
    public function setQuery($query);

    /**
     * @return ActiveDataProvider
     */
    public function getDataProvider();
}