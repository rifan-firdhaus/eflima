<?php namespace modules\project\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\BaseSettingObject;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class SettingObject extends BaseSettingObject
{
    /**
     * @inheritdoc
     */
    public function render()
    {
        switch ($this->renderer->section) {
            case 'finance':
                $this->renderProjectSection();
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        switch ($this->renderer->section) {
            case 'project':
                $this->initProjectSection();
                break;
        }
    }

    protected function initProjectSection()
    {
        $this->renderer->view->on('block:core/admin/setting/index:begin', function () {
            echo $this->renderer->view->render('@modules/project/views/admin/setting/menu', ['active' => 'project']);
        });
    }

    protected function renderProjectSection()
    {
    }
}