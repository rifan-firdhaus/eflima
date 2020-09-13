<?php namespace modules\ui\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\ui\assets\TinyMceAsset;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TinyMceInput extends InputWidget
{
    const TYPE_BASIC = 'basic';
    const TYPE_FLOATING = 'floating';
    const TYPE_ADVANCED = 'advanced';

    public $type = self::TYPE_ADVANCED;
    public $inline = false;

    public $options = [];
    public $jsOptions = [];

    public function init()
    {
        $this->registerAssets();

        if ($this->type) {
            $this->jsOptions = ArrayHelper::merge(self::types($this->type), $this->jsOptions);
        }

        parent::init();
    }

    public function registerAssets()
    {
        TinyMceAsset::register($this->view);
    }

    public function types($type = false)
    {
        $types = [
            self::TYPE_BASIC => [
                'auto_focus' => false,
                'plugins' => [
                    'autoresize quickbars lists link image code imagetools paste codesample textpattern'
                ],
                'menubar' => false,
                'statusbar' => false,
                'quickbars_insert_toolbar' => false,
                'toolbar_drawer' => 'floating',
                'quickbars_selection_toolbar' => 'bold underline italic strikethrough subscript superscript | h1 h2 h3 h4 | forecolor | quicklink',
                'toolbar1' => 'bold underline italic strikethrough subscript superscript | forecolor backcolor | fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | image link codesample'
            ],
            self::TYPE_FLOATING => [
                'auto_focus' => false,
                'plugins' => [
                    'autoresize quickbars lists link image code imagetools paste codesample textpattern'
                ],
                'menubar' => false,
                'statusbar' => false,
                'quickbars_insert_toolbar' => false,
                'toolbar_drawer' => 'floating',
                'quickbars_selection_toolbar' => 'bold underline italic strikethrough subscript superscript | forecolor | quicklink',
                'toolbar' => false,
            ],
            self::TYPE_ADVANCED => [
                'auto_focus' => false,
                'plugins' => [
                    'advlist fullscreen autolink autoresize lists link image charmap hr anchor pagebreak',
                    'searchreplace wordcount visualblocks visualchars code fullscreen',
                    'insertdatetime media nonbreaking save table directionality',
                    'emoticons template paste textpattern imagetools codesample toc',
                ],
                'toolbar1' => 'bold underline italic strikethrough subscript superscript | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
                'toolbar2' => 'fullscreen code searchreplace | styleselect fontselect fontsizeselect | table image link unlink blockquote codesample',
                'image_advtab' => true,
                'autoresize_max_height' => 500,
                'autoresize_min_height' => 200,
                'autoresize_bottom_margin' => 0,
            ],
        ];
        if ($type !== false) {
            return isset($types[$type]) ? $types[$type] : [];
        }

        return $types;

    }

    public function run()
    {
        $this->jsOptions['relative_urls'] = false;
        $this->jsOptions['remove_script_host'] = false;
        //        $this->jsOptions['file_manager_url'] = Url::to(['/file_manager/staff/file-manager/explore', 'multiselect' => 0, 'picker' => 'tinymceFilePickCallback']);
        //        $this->jsOptions['file_picker_callback'] = new JsExpression('tinymceFilePickerCallback');
        //        $this->view->registerJs($this->renderFile('@modules/core/form/tinymce/assets/file_manager.js'), View::POS_HEAD);
        //
        if ($this->options['placeholder']) {
            $this->jsOptions['setup'] = new JsExpression(
            /** @lang JavaScript */
                "function(editor) {
                        editor.on('init', function () {
                            if ( editor.getContent() === \"\" ) {
                                 tinymce.DOM.addClass( editor.bodyElement, 'empty' );
                             } else {
                                 tinymce.DOM.removeClass( editor.bodyElement, 'empty' );
                             }
                        });
            
                         editor.on('selectionchange', function () {
                             if ( editor.getContent() === \"\" ) {
                                 tinymce.DOM.addClass( editor.bodyElement, 'empty' );
                             } else {
                                 tinymce.DOM.removeClass( editor.bodyElement, 'empty' );
                             }
                         });
                     }
                 ");
        }

        if ($this->inline) {
            $editableOptions = $this->options;
            $editableOptions['id'] = $this->options['id'] . '-editable';

            $editable = Html::tag('div', Html::getAttributeValue($this->model, $this->attribute), $editableOptions);
            $input = Html::activeHiddenInput($this->model, $this->attribute, $this->options);

            $this->jsOptions['selector'] = '#' . $editableOptions['id'];
            $this->jsOptions['inline'] = true;
            $originalSetup = isset($this->jsOptions['setup']) ? $this->jsOptions['setup'] : '$.noop';
            $this->jsOptions['setup'] = new JsExpression('function(editor){editor.on("change KeyUp",function(){$("#' . $this->options['id'] . '").val(this.getContent())});var originalSetup = ' . $originalSetup . '; if(typeof originalSetup == "function"){originalSetup.apply(this,[editor])}}');
            $this->view->registerJs("tinymce.init(" . Json::encode($this->jsOptions) . ");");

            return $editable . $input;
        }

        $this->view->registerJs("$( '#{$this->options['id']}').tinymce(" . Json::encode($this->jsOptions) . ");");

        return Html::activeTextarea($this->model, $this->attribute, $this->options);
    }
}