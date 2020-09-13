<?php namespace modules\core\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\View as AdminView;
use modules\core\models\forms\SettingForm;
use modules\core\web\Controller;
use modules\core\web\View;
use modules\file_manager\web\UploadedFile;
use modules\ui\widgets\form\fields\CardField;
use modules\ui\widgets\form\Form;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\ModelEvent;
use yii\db\Exception as DbException;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property SettingForm[] $models
 */
class SettingRenderer extends Component
{
    const EVENT_INIT = 'eventInit';
    const EVENT_RENDER = 'eventRender';
    const EVENT_BEFORE_SAVE = 'eventBeforeSave';
    const EVENT_AFTER_SAVE = 'eventAfterSave';

    public $section;

    /** @var View */
    public $view;

    /** @var array|CardField[] */
    public $subSections = [];

    /** @var Form */
    public $form;

    /** @var array */
    public $formOptions;

    /** @var array */
    protected $_objects = [];

    /** @var array */
    protected $_fields = [];

    /** @var SettingForm[] */
    protected $_models = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->trigger(self::EVENT_INIT);
    }

    /**
     * @param SettingObject|string|array $object
     *
     * @return $this
     * @throws InvalidConfigException
     */
    public function addObject($object)
    {
        $object = Yii::createObject([
            'class' => $object,
            'renderer' => $this,
        ]);

        if (!$object instanceof BaseSettingObject) {
            throw new InvalidArgumentException('SettingObject must be instance of' . BaseSettingObject::class);
        }


        $this->_objects[] = $object;

        return $this;
    }

    /**
     * @param array $fields
     *
     * @return $this
     */
    public function addFields($fields)
    {
        foreach ($fields AS $id => $field) {
            $this->addField($id, $field);
        }

        return $this;
    }

    /**
     * @param string $id
     * @param array  $field
     *
     * @return $this
     */
    public function addField($id, $field)
    {
        $this->_fields[$id] = $field;

        return $this;
    }

    /**
     * @param string $id
     * @param array  $options
     */
    public function addSubSection($id, $options = [])
    {
        $this->subSections[$id] = $options;
    }

    /**
     * @param string $id
     *
     * @return array|CardField
     */
    public function getSubSection($id)
    {
        if (!isset($this->subSections[$id])) {
            throw new InvalidArgumentException("Subsection is not registered");
        }

        return $this->subSections[$id];
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getField($id)
    {
        if (!isset($this->_fields[$id])) {
            throw new InvalidArgumentException("Field {$id} is not registered");
        }

        return $this->_fields[$id];
    }

    /**
     * @param $id
     *
     * @return SettingForm
     * @throws InvalidConfigException
     */
    public function getModel($id)
    {
        if (empty($this->_models)) {
            $this->getModels();
        }

        if (!isset($this->_models[$id])) {
            throw new InvalidArgumentException("Please set a rules for this model before you can get");
        }

        return $this->_models[$id];
    }

    /**
     * @return SettingForm[]
     * @throws InvalidConfigException
     */
    public function getModels()
    {
        if (!$this->_models) {
            $this->_models = SettingForm::find()->andWhere(['id' => array_keys($this->_fields)])->indexBy('id')->all();

            foreach ($this->_models AS $model) {
                $model->renderer = $this;
            }
        }

        return $this->_models;
    }

    /**
     * @param Controller $controller
     * @param array      $data
     *
     * @return array|Response|bool
     *
     * @throws DbException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function loadAndSave($controller, $data = [])
    {
        if (Model::loadMultiple($this->getModels(), $data, 'Setting')) {
            $this->loadFiles();

            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                $messages = [];

                foreach ($this->getModels() AS $model) {
                    $messages = ArrayHelper::merge($messages, Form::validate($model));
                }

                return $messages;
            }

            if ($this->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} successfully saved', [
                    'object' => Yii::t('app', 'Settings'),
                ]));

                return $controller->refresh();
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'settings'),
                ]));
            }
        }

        return false;
    }

    /**
     * @throws InvalidConfigException
     */
    protected function loadFiles()
    {
        foreach ($this->getModels() AS $model) {
            if (!$model->is_file) {
                continue;
            }

            $model->uploaded_value = UploadedFile::getInstance($model, 'uploaded_value');
        }
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws DbException
     */
    public function save()
    {
        $event = new ModelEvent();

        $this->trigger(self::EVENT_BEFORE_SAVE, $event);

        if ($event->isValid && SettingForm::saveAll($this->getModels())) {
            $this->trigger(self::EVENT_AFTER_SAVE);

            return true;
        }

        return false;
    }

    /**
     * @throws InvalidConfigException
     */
    public function render()
    {
        /** @var AdminView $view */
        $this->form = Form::begin($this->formOptions);
        $view = Yii::$app->view;

        $view->mainForm($this->form);

        foreach ($this->subSections AS $id => $subSection) {
            $subSection['form'] = $this->form;
            $this->subSections[$id] = Yii::createObject(CardField::class, [$subSection]);
        }

        $this->trigger(self::EVENT_RENDER);

        foreach ($this->_objects AS $object) {
            $object->render();
        }

        foreach ($this->subSections AS $subSection) {
            echo $subSection;
        }

        Form::end();
    }
}
