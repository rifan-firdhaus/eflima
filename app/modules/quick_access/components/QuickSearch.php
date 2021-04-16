<?php namespace modules\quick_access\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\View;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\data\BaseDataProvider;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
abstract class QuickSearch
{
    /** @var QuickSearch[] */
    protected static $searchables = [];

    /**
     * @return string
     */
    abstract public function getLabel();

    /**
     * @return string
     */
    abstract public function getId();

    /**
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * @param $q
     *
     * @return BaseDataProvider
     */
    abstract public function search($q);

    /**
     * @param mixed $model
     * @param View  $view
     *
     * @return string
     */
    abstract public function render($model, $view);

    /**
     * @param $object
     *
     * @throws InvalidConfigException
     */
    public static function register($object)
    {
        $searchable = Yii::createObject($object);

        if (!($searchable instanceof QuickSearch)) {
            throw new InvalidArgumentException();
        }

        self::$searchables[] = $searchable;
    }

    public static function map()
    {
        return ArrayHelper::map(
            array_filter(
                self::$searchables,
                function ($searchable) {
                    /** @var QuickSearch $searchable */

                    return $searchable->isActive();
                }
            ),
            function ($searchable) {
                /** @var QuickSearch $searchable */

                return $searchable->getId();
            },
            function ($searchable) {
                /** @var QuickSearch $searchable */

                return $searchable->getLabel();
            }
        );
    }

    /**
     * @param string   $q
     * @param string[] $models
     * @param View     $view
     *
     * @return array
     */
    public static function run($q, $models = [], $view)
    {
        $result = [
            'page' => 1,
            'more' => false,
            'result' => [],
        ];

        foreach (self::$searchables AS $searchable) {
            if (!in_array($searchable->getId(), $models) || !$searchable->isActive()) {
                continue;
            }

            $dataProvider = $searchable->search($q);

            if ($dataProvider->pagination) {
                $dataProvider->pagination->pageParam = 'page';
                $dataProvider->pagination->pageSize = 8;

                if ($dataProvider->pagination->pageCount > 0 && $dataProvider->pagination->page != $dataProvider->pagination->pageCount - 1) {
                    $result['more'] = true;
                }
            }

            $result['result'][$searchable->getId()] = [
                'id' => $searchable->getId(),
                'label' => $searchable->getLabel(),
                'result' => [],
            ];

            foreach ($dataProvider->models AS $model) {
                $result['result'][$searchable->getId()]['result'][] = $searchable->render($model, $view);
            }
        }

        return $result;
    }
}
