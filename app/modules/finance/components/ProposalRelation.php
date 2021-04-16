<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\Proposal;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property-read string $label
 * @property-read array   $address
 * @property-read object $model
 */
abstract class ProposalRelation extends Component
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
     * @param Proposal $proposal
     * @param string   $attribute
     *
     * @return string
     */
    public function pickerInput($proposal, $attribute)
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
     * @param mixed    $model
     * @param Proposal $proposal
     *
     * @return void
     */
    public function validate($model, $proposal)
    {
        return;
    }

    /**
     * @param string                        $id
     * @param string|array|ProposalRelation $class
     */
    public static function register($id, $class)
    {
        self::$relations[$id] = $class;
    }

    /**
     * @param object $model
     *
     * @return array
     */
    public function getAddress($model)
    {
        return [
            'province' => null,
            'city' => null,
            'country' => null,
            'address' => null,
            'postal_code' => null,
        ];
    }

    /**
     * @param $id
     *
     * @return ProposalRelation
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
     * @return array|ProposalRelation[]
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
