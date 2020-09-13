<?php namespace modules\quick_access\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\View;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\data\BaseDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
abstract class QuickSearch
{
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
     */
    public static function register($object)
    {
        self::$searchables[] = $object;
    }

    /**
     * @param string $q
     * @param View   $view
     *
     * @return array
     * @throws InvalidConfigException
     */
    public static function run($q, $view)
    {
        $result = [
            'page' => 1,
            'more' => false,
            'result' => []
        ];

        foreach (self::$searchables AS $searchable) {
            /** @var QuickSearch|mixed $searchable */
            $searchable = Yii::createObject($searchable);

            if (!($searchable instanceof QuickSearch)) {
                throw new InvalidArgumentException();
            }

            $dataProvider = $searchable->search($q);

            if ($dataProvider->pagination) {
                $dataProvider->pagination->pageParam = 'page';
                $dataProvider->pagination->pageSize = 8;

                if($dataProvider->pagination->pageCount > 0 && $dataProvider->pagination->page != $dataProvider->pagination->pageCount - 1){
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