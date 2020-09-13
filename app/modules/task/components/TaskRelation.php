<?php namespace modules\task\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\task\models\Task;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property string $label
 * @property object $model
 */
abstract class TaskRelation extends Component
{
    protected static $relations = [];
    protected static $instances = [];

    public $useDefaultPicker = true;

    /**
     * @return string
     */
    abstract public function getLabel();

    /**
     * @param string $id
     *
     * @return mixed
     */
    abstract public function getModel($id);

    /**
     * @param mixed $model
     *
     * @return mixed
     */
    abstract public function getName($model);

    /**
     * @param string     $term
     * @param int|string $page
     *
     * @return array
     */
    public function autoComplete($term, $page = 1)
    {
        return [];
    }

    /**
     * @param Task   $task
     * @param string $attribute
     *
     * @return string
     */
    public function pickerInput($task, $attribute)
    {
        return '';
    }

    /**
     * @param mixed $model
     *
     * @return null
     */
    public function getUrl($model)
    {
        return null;
    }

    /**
     * @param mixed $model
     *
     * @return null
     */
    public function getLink($model)
    {
        return null;
    }

    /**
     * @param mixed $model
     * @param Task  $task
     *
     * @return void
     */
    public function validate($model, $task)
    {
        return;
    }

    /**
     * @param string                    $id
     * @param string|array|TaskRelation $class
     */
    public static function register($id, $class)
    {
        self::$relations[$id] = $class;
    }

    /**
     * @param $id
     *
     * @return TaskRelation
     * @throws InvalidConfigException
     */
    public static function get($id)
    {
        if (!isset(self::$relations[$id])) {
            throw new InvalidArgumentException("Relation with id: {$id} doesn't exists");
        }

        if (!isset(self::$instances[$id])) {
            $options = [];
            $class = self::$relations[$id];

            if (is_array($class)) {
                $options = self::$relations[$id];
                $class = ArrayHelper::remove($options, 'class');
            }

            self::$instances[$id] = Yii::createObject($class, $options);
        }

        return self::$instances[$id];
    }

    /**
     * @return array|TaskRelation[]
     * @throws InvalidConfigException
     */
    public static function all()
    {
        $relations = [];

        foreach (self::$relations AS $id => $option) {
            $relations[$id] = self::get($id);
        }

        return $relations;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public static function map()
    {
        $relations = [];

        foreach (self::all() AS $id => $relation) {
            $relations[$id] = $relation->getLabel();
        }

        return $relations;
    }
}