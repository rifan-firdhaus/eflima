<?php namespace modules\ui\widgets\form\fields;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\ui\widgets\Card;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CardField extends MultiField
{
    /** @var array|Card */
    public $card = [
        'class' => Card::class,
    ];

    public $options = [];
    public $inputOptions = [];

    /**
     * @return array|Card
     */
    protected function createCard()
    {
        $class = ArrayHelper::remove($this->card, 'class', Card::class);

        $this->card['autoRender'] = false;

        $this->card = $class::begin($this->card);
        $class::end();

        return $this->card;
    }

    /**
     * @inheritdoc
     */
    public function label()
    {
        return $this->card->renderHeader();
    }

    /**
     * @inheritdoc
     */
    public function input()
    {
        return $this->card->renderBody($this->renderFields()) . $this->card->renderFooter();
    }
    /**
     * @inheritdoc
     */
    protected function begin()
    {
        return $this->card->beginTag();
    }

    /**
     * @inheritdoc
     */
    protected function end()
    {
        return $this->card->endTag();
    }

    /**
     * @inheritdoc
     */
    protected function normalize()
    {
        parent::normalize();

        $this->createCard();

        $this->card->title = $this->label;
        $this->card->options = ArrayHelper::merge($this->card->options, $this->options);
        $this->card->bodyOptions = ArrayHelper::merge($this->card->bodyOptions, $this->inputOptions);

    }
}