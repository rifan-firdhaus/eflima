<?php namespace modules\ui\widgets\table;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\helpers\Html;
use modules\ui\widgets\table\sections\Body;
use modules\ui\widgets\table\sections\Footer;
use modules\ui\widgets\table\sections\Header;
use modules\ui\widgets\table\sections\Section;
use Yii;
use yii\base\Widget;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Table extends Widget
{
    /** @var array|Section */
    public $body = [
        'class' => Body::class,
    ];

    /** @var array|Section */
    public $footer = [
        'class' => Footer::class,
    ];

    /** @var array|Header */
    public $header = [
        'class' => Header::class,
    ];

    public $options = [
        'class' => 'table',
    ];

    public $tagName = 'table';

    public function init()
    {
        $this->body['grid'] = $this;
        $this->footer['grid'] = $this;
        $this->header['grid'] = $this;

        if (!isset($this->body['class'])) {
            $this->body['class'] = Body::class;
        }

        if (!isset($this->footer['class'])) {
            $this->footer['class'] = Footer::class;
        }

        if (!isset($this->header['class'])) {
            $this->header['class'] = Header::class;
        }

        $this->body = Yii::createObject($this->body);
        $this->footer = Yii::createObject($this->footer);
        $this->header = Yii::createObject($this->header);

        parent::init();
    }

    public function run()
    {
        $content = $this->header->render() . $this->body->render() . $this->footer->render();

        return Html::tag($this->tagName, $content, $this->options);
    }
}