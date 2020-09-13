<?php namespace modules\core\models\forms;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\core\components\SettingRenderer;
use modules\core\models\Setting;
use modules\file_manager\behaviors\FileUploaderBehavior;
use Throwable;
use yii\db\Exception as DbException;
use yii\helpers\Inflector;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property SettingRenderer $renderer
 * @property array           $options
 */
class SettingForm extends Setting
{
    /** @var SettingRenderer */
    protected $_renderer;

    protected $_options;

    /**
     * @param SettingForm[] $models
     *
     * @return bool
     * @throws Throwable
     * @throws DbException
     */
    public static function saveAll($models)
    {
        if (!self::validateMultiple($models)) {
            return false;
        }

        $transaction = self::getDb()->beginTransaction();

        try {
            foreach ($models AS $model) {
                if (!$model->save(false)) {
                    $transaction->rollBack();

                    return false;
                }
            }
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        $transaction->commit();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = $this->getOption('rules', []);

        foreach ($rules AS $key => $rule) {
            $rules[$key] = (array) $rules[$key];

            if ($this->is_file) {
                array_unshift($rules[$key], 'uploaded_value');
            } else {
                array_unshift($rules[$key], 'value');
            }
        }

        return $rules;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed|null
     */
    public function getOption($key, $default = null)
    {
        $options = $this->getOptions();
        $value = isset($options[$key]) ? $options[$key] : null;

        if (isset($value)) {
            return $value;
        }

        return isset($default) ? $default : $value;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->renderer->getField($this->id);
        }

        return $this->_options;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['value'] = $this->getOption('label', Inflector::humanize($this->id));
        $labels['uploaded_value'] = $labels['value'];

        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        $labels = parent::attributeLabels();

        $labels['value'] = $this->getOption('hint', '');
        $labels['uploaded_value'] = $labels['value'];

        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return "Setting[{$this->id}]";
    }

    /**
     * @return SettingRenderer
     */
    public function getRenderer()
    {
        return $this->_renderer;
    }

    /**
     * @param SettingRenderer $renderer
     */
    public function setRenderer($renderer)
    {
        $this->_renderer = $renderer;

        $isFile = $this->getOption('isFile', false);

        if ($isFile) {
            $this->attachBehavior('fileUploader', [
                'class' => FileUploaderBehavior::class,
                'attributes' => [
                    'value' => [
                        'alias' => 'uploaded_value',
                        'base_path' => '@webroot/protected/system/setting',
                        'base_url' => '@web/protected/system/setting',
                    ],
                ],
            ]);
        }
    }
}