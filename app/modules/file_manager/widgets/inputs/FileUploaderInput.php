<?php namespace modules\file_manager\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\base\InputWidget;
use modules\file_manager\assets\FileUploaderAsset;
use yii\helpers\Html;
use modules\ui\widgets\JQueryWidgetTrait;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class FileUploaderInput extends InputWidget
{
    use JQueryWidgetTrait;

    public $jsOptions = [];
    public $multiple = false;

    /**
     * @inheritdoc
     */
    public function init()
    {

        if (!empty($this->options['placeholder']) && !isset($this->jsOptions['texts']['empty'])) {
            $this->jsOptions['texts']['empty'] = $this->options['placeholder'];
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->normalize();
        $this->registerAssets();

        if ($this->hasModel()) {
            return Html::activeFileInput($this->model, $this->attribute, $this->options);
        }

        return Html::fileInput($this->name, $this->value, $this->options);
    }

    public function normalize()
    {
        $this->jsOptions['multiple'] = $this->multiple;

        if ($this->hasModel() && $this->model->hasMethod('getFileMetadata')) {
            $metadata = $this->model->getFileMetadata($this->model->getFileAttributeByAlias($this->attribute));

            if ($metadata) {
                $this->jsOptions['values'][] = $metadata;
            }
        }
    }

    public function registerAssets()
    {
        FileUploaderAsset::register($this->view);

        $this->registerPlugin('fileUploader');
    }
}