<?php namespace modules\ui\widgets\form\fields;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\ui\widgets\Tabs;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TabsField extends Field
{
    /** @var array|Tabs */
    public $tabs = [
        'class' => Tabs::class
    ];

    public $items = [];

    public $inputOnly = true;

    /**
     * @inheritdoc
     */
    public function input()
    {
        foreach ($this->items AS $tab) {
            $fields = ArrayHelper::remove($tab, 'fields', []);
            $id = ArrayHelper::getValue($tab, 'id');
            $options = ArrayHelper::getValue($tab, 'options', []);
            $navigation = ArrayHelper::getValue($tab, 'navigation', []);
            $options['content'] = $this->form->fields($fields);

            $this->tabs->addItem($id, $navigation, $options);
        }

        return $this->tabs->renderNavigation() . $this->tabs->renderContent();
    }

    /**
     * @inheritdoc
     */
    public function label()
    {
        return '';
    }

    /**
     * @return Tabs
     */
    public function createTabs()
    {
        $class = ArrayHelper::remove($this->tabs, 'class', Tabs::class);

        $this->tabs['autoRender'] = false;

        $this->tabs = $class::begin($this->tabs);

        $class::end();

        return $this->tabs;
    }

    /**
     * @inheritdoc
     */
    protected function normalize()
    {
        $this->createTabs();

        parent::normalize();
    }
}