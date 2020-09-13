<?php namespace modules\finance\components;

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
                $this->renderFinanceSection();
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        switch ($this->renderer->section) {
            case 'finance':
                $this->initFinanceSection();
                break;
        }
    }

    protected function initFinanceSection()
    {
        $this->renderer->view->on('block:core/admin/setting/index:begin', function () {
            echo $this->renderer->view->render('@modules/finance/views/admin/setting/menu', ['active' => 'task-setting']);
        });
    }

    protected function renderFinanceSection()
    {
    }
}