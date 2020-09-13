<?php namespace modules\support\components;

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
            case 'ticket':
                $this->renderTicketSection();
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        switch ($this->renderer->section) {
            case 'ticket':
                $this->initTicketSection();
                break;
        }
    }

    protected function initTicketSection()
    {
        $this->renderer->view->on('block:core/admin/setting/index:begin', function () {
            echo $this->renderer->view->render('@modules/support/views/admin/setting/menu', ['active' => 'ticket-setting']);
        });
    }

    protected function renderTicketSection()
    {

    }
}