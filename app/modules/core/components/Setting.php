<?php namespace modules\core\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\models\Setting as SettingModel;
use modules\core\validators\DateRangeValidator;
use modules\core\validators\DateValidator;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\caching\ArrayCache;
use yii\caching\Cache;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Setting extends Component
{
    /** @var array|Cache */
    protected $cache = [
        'class' => ArrayCache::class,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->cache = Yii::createObject($this->cache);

        $this->autoLoad();
        $this->setEnvironment();
    }

    /**
     * Add all autoload settings to cache
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    protected function autoLoad()
    {
        $settings = SettingModel::find()->andWhere(['setting.is_autoload' => true])
            ->select(['setting.id', 'setting.value'])
            ->createCommand()
            ->queryAll();

        $this->cache->multiSet(ArrayHelper::map($settings, 'id', 'value'));
    }

    /**
     * Set app settings
     *
     * @throws InvalidConfigException
     */
    protected function setEnvironment()
    {
        $numberFormats = [
            'period' => ['thousandSeparator' => '.', 'decimalSeparator' => ','],
            'comma' => ['thousandSeparator' => ',', 'decimalSeparator' => '.'],
        ];
        $numberFormat = $numberFormats[$this->get('number_format')];

        Yii::$app->setTimeZone($this->get('timezone'));
        Yii::$app->formatter->timeZone = $this->get('timezone');
        Yii::$app->formatter->dateFormat = $this->get('date_display_format');
        Yii::$app->formatter->timeFormat = $this->get('time_display_format');
        Yii::$app->formatter->datetimeFormat = $this->get('datetime_display_format');
        Yii::$app->formatter->thousandSeparator = $numberFormat['thousandSeparator'];
        Yii::$app->formatter->decimalSeparator = $numberFormat['decimalSeparator'];

        Validator::$builtInValidators['date'] = DateValidator::class;
        Validator::$builtInValidators['datetime'] = [
            'class' => DateValidator::class,
            'type' => DateValidator::TYPE_DATETIME,
        ];
        Validator::$builtInValidators['time'] = [
            'class' => DateValidator::class,
            'type' => DateValidator::TYPE_TIME,
        ];
        Validator::$builtInValidators['daterange'] = [
            'class' => DateRangeValidator::class,
        ];
    }

    /**
     * Get the value of setting by it's key
     *
     * @param string     $id
     * @param null|mixed $default
     *
     * @return mixed|null
     * @throws InvalidConfigException
     */
    public function get($id, $default = null)
    {
        $value = $this->cache->get($id);

        if ($value === false) {
            $model = SettingModel::find()->select('value')->asArray()->andWhere(['id' => $id])->one();

            $value = $model ? $model['value'] : null;

            $this->cache->set($id, $value);
        }

        return $value ? $value : $default;
    }

    /**
     * Set settings value
     *
     * @param string $id
     * @param mixed  $value
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function set($id, $value)
    {
        $model = SettingModel::find()->andWhere(['id' => $id])->one();

        if (!$model) {
            throw new InvalidArgumentException("Setting with id \"{$id}\" doesn't exists");
        }

        $model->value = $value;

        if ($model->save(false)) {
            $this->cache->set($id, $value);

            return true;
        }

        return false;
    }
}