<?php namespace modules\ui\widgets\form;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveRecord;
use modules\ui\assets\EflimaFormAsset;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\Field;
use modules\ui\widgets\form\fields\RegularField;
use modules\ui\widgets\Icon;
use modules\ui\widgets\lazy\Lazy;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Widget;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Form extends Widget
{
    const LAYOUT_HORIZONTAL = 'horizontal';
    const LAYOUT_VERTICAL = 'vertical';
    const LAYOUT_INLINE = 'inline';
    public static $fieldClasses = [
        'default' => RegularField::class,
        'active' => ActiveField::class,
    ];
    public $options = [
        'enctype' => 'multipart/form-data',
    ];
    public $action = '';
    public $method = 'post';
    public $enableClient = true;
    public $enableClientValidation = true;
    public $enableAjaxValidation = true;
    public $fields = [];
    public $fieldVisibility = [];
    public $actions = [];
    public $lazy = [
        'class' => Lazy::class,
    ];
    public $defaultFieldClass = ActiveField::class;


    /** @var Model */
    public $model;

    public $layout = self::LAYOUT_HORIZONTAL;
    public $autoRenderActions = true;
    public $formActionsOptions = [
        'class' => 'form-action',
    ];
    public $formActionsSections = [];
    public $enableTimestamp = true;
    /** @var Lazy */
    protected $lazyClass;

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }

        if (!isset($this->options['data-rid'])) {
            $this->options['data-rid'] = $this->getRealId();
        }

        if ($this->lazy !== false) {
            $this->lazyClass = $lazyClass = ArrayHelper::remove($this->lazy, 'class', Lazy::class);
            $this->lazy['type'] = 'form';

            if (!isset($this->lazy['id'])) {
                $this->lazy['id'] = $this->getRealId() . '-lazy';
            }

            $lazyClass::begin($this->lazy);
        }

        if ($this->enableTimestamp && $this->model && ($timestamp = $this->model->getBehavior('timestamp'))) {
            /** @var TimestampBehavior $timestamp */

            if ($timestamp->createdAtAttribute && ($this->model instanceof ActiveRecord && !$this->model->isNewRecord)) {
                $this->addAction(
                    Html::tag(
                        'div',
                        Icon::show('i8:calendar-plus', ['class' => 'mr-i icons8-size']) .
                        Yii::t('app', 'Created at') .
                        Html::tag('strong', Yii::$app->formatter->asDatetime($this->model->{$timestamp->createdAtAttribute})), [
                            'class' => 'timestamp-created-at',
                        ]
                    ),
                    'created_at',
                    'timestamp'
                );
            }

            if ($timestamp->updatedAtAttribute && ($this->model instanceof ActiveRecord && !$this->model->isNewRecord)) {
                $this->addAction(
                    Html::tag(
                        'div',
                        Icon::show('i8:date-span', ['class' => 'mr-i icons8-size']) .
                        Yii::t('app', 'Updated at') .
                        Html::tag('strong', Yii::$app->formatter->asDatetime($this->model->{$timestamp->updatedAtAttribute})), [
                            'class' => 'timestamp-updated-at',
                        ]
                    ),
                    'updated_at',
                    'timestamp'
                );
            }
        }

        if (!isset($this->actions['primary']['save'])) {
            $this->addAction(Html::submitButton(Icon::show('i8:paper-plane') . Yii::t('app', 'Save'), [
                'class' => 'btn btn-primary',
                'data-lazy-submit-button' => (bool) $this->lazy,
            ]), 'save');
        }

        if (!isset($this->formActionsSections['primary'])) {
            $this->formActionsSections['primary'] = [
                'class' => 'align-self-center ml-auto',
            ];
        }

        if (!isset($this->formActionsSections['timestamp'])) {
            $this->formActionsSections['timestamp'] = [
                'class' => 'align-self-start d-flex flex-column timestamp',
            ];
        }

        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * @param string|array $action
     * @param null         $key
     * @param string       $section
     * @param int          $sort
     *
     * @return $this
     */
    public function addAction($action, $key = null, $section = 'primary', $sort = 99)
    {
        if (is_null($key)) {
            $key = isset($this->actions[$section]) ? 0 : count($this->actions[$section]);
        }

        $this->actions[$section][$key] = compact('sort', 'action');

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->normalize();

        $content = ob_get_clean();

        if ($this->enableClient) {
            $this->registerAssets();
        }

        $actions = '';

        if ($this->autoRenderActions && !empty($this->actions)) {
            $actions = $this->renderActions();
        }

        $result = $this->beginTag() . $content . $actions . $this->endTag();

        echo $result;

        if ($this->lazy !== false) {
            $lazyClass = $this->lazyClass;

            $lazyClass::end();
        }
    }

    /**
     * @return void
     */
    protected function normalize()
    {
        $this->options['method'] = $this->method;
        $this->options['action'] = Url::to($this->action);

        if ($this->layout === self::LAYOUT_INLINE) {
            Html::addCssClass($this->options, 'form-inline');
        }
    }

    /**
     * This registers the necessary JavaScript code.
     */
    public function registerAssets()
    {
        $id = $this->options['id'];
        $options = Json::htmlEncode($this->getClientOptions());
        $view = $this->getView();

        EflimaFormAsset::register($view);

        $view->registerJs("jQuery('#$id').eflimaForm($options);");
    }

    /**
     * @return array
     */
    public function getClientOptions()
    {
        $options = [];

        $options['fields'] = $this->fields;

        return $options;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function renderActions()
    {
        $sections = [];

        foreach ($this->actions AS $section => $actions) {
            $actionsHtml = '';

            $sections[$section]['sort'] = isset($this->formActionsSections[$section]['sort']) ? $this->formActionsSections[$section]['sort'] : 10;

            ArrayHelper::multisort($actions, 'sort');

            foreach ($actions AS $action) {
                if (is_array($action['action'])) {
                    $action['action'] = Html::a($action['action']);
                }

                $actionsHtml .= $action['action'];
            }

            $sectionOptions = isset($this->formActionsSections[$section]) ? $this->formActionsSections[$section] : [];

            $sections[$section]['value'] = Html::tag('div', $actionsHtml, $sectionOptions);
        }

        ArrayHelper::multisort($sections, 'sort');

        $result = implode('', ArrayHelper::getColumn($sections, 'value'));

        return Html::tag('div', $result, $this->formActionsOptions);
    }

    /**
     * @return string
     */
    public function beginTag()
    {
        return Html::beginForm($this->action, $this->method, $this->options);
    }

    /**
     * @return string
     */
    public function endTag()
    {
        return Html::endForm();
    }

    /**
     * @param array|Field[] $fields
     *
     * @return string
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function fields($fields = [])
    {
        $result = [];

        foreach ($fields AS $field) {
            $field = $this->field($field);

            $result[] = [
                'sort' => $field->sort,
                'field' => $field->render(),
            ];
        }

        ArrayHelper::multisort($result, 'sort');

        return implode('', ArrayHelper::getColumn($result, 'field'));
    }

    /**
     * @param $options
     *
     * @return Field
     * @throws InvalidConfigException
     */
    public function field($options = [])
    {
        if ($options instanceof Field) {
            return $options;
        }

        if (is_string($options)) {
            $options = [
                'attribute' => $options,
                'class' => ActiveField::class,
            ];
        }

        if (!isset($options['class'])) {
            $options['class'] = $this->defaultFieldClass;
        }

        $options['form'] = $this;

        $field = Yii::createObject($options);

        if (!$field instanceof Field) {
            throw new InvalidConfigException("Field must be instance of " . Field::class);
        }

        return $field;
    }

    /**
     * @param Model $model
     * @param array $fields
     *
     * @return string
     *
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function activeFields($model, $fields = [])
    {
        $result = [];

        foreach ($fields AS $id => $field) {
            if (!isset($field['class']) || $field['class'] === ActiveField::class) {
                if (!isset($field['attribute']) && is_int($id)) {
                    throw new InvalidConfigException("You have to set attribute");
                }

                $attribute = isset($field['attribute']) ? $field['attribute'] : $id;

                $field = $this->activeField($model, $attribute, $field);
            } else {
                $field = $this->field($field);
            }

            $result[] = [
                'sort' => $field->sort,
                'field' => $field->render(),
            ];
        }

        ArrayHelper::multisort($result, 'sort');

        return implode('', ArrayHelper::getColumn($result, 'field'));
    }

    /**
     * @param Model  $model
     * @param string $attribute
     * @param array  $options
     *
     * @return ActiveField
     * @throws InvalidConfigException
     */
    public function activeField($model, $attribute, $options = [])
    {
        if (!isset($options['class'])) {
            $options['class'] = ActiveField::class;
        }

        /** @var ActiveField $field */
        $field = $this->field($options);

        $field->model = $model;
        $field->attribute = $attribute;

        return $field;
    }

    public static function validate($model, $attributes = null)
    {
        $result = [];
        if ($attributes instanceof Model) {
            // validating multiple models
            $models = func_get_args();
            $attributes = null;
        } else {
            $models = [$model];
        }
        /* @var $model Model */
        foreach ($models as $model) {
            $model->validate($attributes);
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getRealInputId($model, $attribute)] = $errors;
            }
        }

        return $result;
    }

    public static function validateMultiple($models, $attributes = null)
    {
        $result = [];
        /* @var $model Model */
        foreach ($models as $i => $model) {
            $model->validate($attributes);
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getRealInputId($model, "[$i]" . $attribute)] = $errors;
            }
        }

        return $result;
    }
}
