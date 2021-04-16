<?php namespace modules\account\web\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\View as BaseView;
use modules\ui\assets\FontAwesomeAsset;
use modules\ui\assets\Icons8Asset;
use modules\ui\components\Menu;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\Icon;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class View extends BaseView
{
    /** @var string|Menu */
    public $menu = Menu::class;
    public $subTitle;
    public $icon;
    public $toolbar = [];
    public $fullHeightContent = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        Icon::register('fa', [
            'prefixClass' => 'fa fa-',
            'options' => [
                'class' => 'icon',
            ],
            'tag' => 'i',
            'asset' => FontAwesomeAsset::class,
        ]);

        $this->menu = Yii::createObject($this->menu);

        parent::init();
    }

    /**
     * @param Form $form
     */
    public function mainForm($form)
    {
        $id = $form->id;

        $this->registerJs("admin.setMainForm('#{$id}')");
    }

    /**
     * @return string
     */
    public function renderToolbar()
    {
        return implode('', $this->toolbar);
    }
}
