<?php namespace modules\account\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\Staff;
use modules\account\web\admin\View;
use Yii;
use yii\base\Component;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class StaffDashboard extends Component
{
    protected $_widgets = [];

    /** @var Staff */
    public $staff;

    /** @var View */
    public $view;

    public function registerWidget($widget)
    {
        $instance = Yii::createObject($widget);

        $this->_widgets[$instance->id] = $instance;
    }

    public function render()
    {

    }
}
