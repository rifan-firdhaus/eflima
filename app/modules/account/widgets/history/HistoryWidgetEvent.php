<?php namespace modules\account\widgets\history;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\History;
use yii\base\Event;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class HistoryWidgetEvent extends Event
{
    /** @var History */
    public $model;
    public $result;
    public $options = [];
    public $icon;
    public $iconOptions = [];
    public $description;
    public $params = [];
}