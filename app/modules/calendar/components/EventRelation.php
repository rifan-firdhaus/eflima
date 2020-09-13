<?php namespace modules\calendar\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\calendar\models\Event;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property string $label
 * @property mixed  $model
 */
abstract class EventRelation extends Component
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
     * @return string
     */
    abstract public function getName($model);


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
     * @param Event $event
     *
     * @return void
     */
    public function validate($model, $event)
    {
        return;
    }


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
     * @param Event  $event
     * @param string $attribute
     *
     * @return string
     */
    public function pickerInput($event, $attribute)
    {
        return '';
    }

    /**
     * @param string                     $id
     * @param string|array|EventRelation $class
     */
    public static function register($id, $class)
    {
        self::$relations[$id] = $class;
    }

    /**
     * @param $id
     *
     * @return EventRelation
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
     * @return array|EventRelation[]
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