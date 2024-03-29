<?php namespace modules\ui\widgets\lazy;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\widgets\lazy\LazyResponse;
use yii\base\Widget;
use modules\ui\widgets\lazy\assets\LazyAsset;
use Yii;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Lazy extends Widget
{
    const TYPE_CONTAINER = 'container';
    const TYPE_FORM = 'form';
    public static $autoIdPrefix = 'lazy-';
    public $options = [];
    public $type = self::TYPE_CONTAINER;
    public $content = '';
    public $jsOptions = [
        'pushState' => true,
    ];

    public static function close()
    {
        LazyResponse::$lazyData['close'] = true;

        Yii::$app->response->headers->add('X-Lazy-Modal-Close', 1);
    }

    /**
     * @return bool
     */
    public static function isLazyModalRequest()
    {
        $headers = Yii::$app->getRequest()->getHeaders();

        return self::isLazyRequest() && $headers->get('X-Lazy-Modal');
    }

    /**
     * @return bool
     */
    public static function isLazyRequest()
    {
        $headers = Yii::$app->getRequest()->getHeaders();

        return $headers->get('X-Lazy') && $headers->get('X-Lazy-Container');
    }

    /**
     * @return bool
     */
    public static function isLazyInsideModalRequest()
    {
        $headers = Yii::$app->getRequest()->getHeaders();

        return $headers->get('X-Lazy-Inside-Modal');
    }

    /**
     * @return array|string
     */
    public static function getLazyContainer()
    {
        $headers = Yii::$app->getRequest()->getHeaders();

        return $headers->get('X-Lazy-Container');
    }

    public function getId($autoGenerate = true)
    {
        return parent::getId($autoGenerate); // TODO: Change the autogenerated stub
    }

    /** @inheritdoc */
    public function init()
    {
        isset($this->options['id']) || ($this->options['id'] = $this->getId());

        $this->options['data-rid'] = $this->getRealId();

        parent::init();

        if (!isset(Yii::$app->response->formatters['lazy'])) {
            Yii::$app->response->formatters['lazy'] = [
                'class' => LazyResponse::class,
            ];
        }

        ob_start();
        ob_implicit_flush(false);

        if ($this->isCurrentLazyRequest()) {
            $this->view->clear();
            $this->view->beginPage();
            $this->view->head();
            $this->view->beginBody();
        }
    }

    /**
     * @return bool
     */
    protected function isCurrentLazyRequest()
    {
        $headers = Yii::$app->getRequest()->getHeaders();

        return $headers->get('X-Lazy') && explode(' ', $headers->get('X-Lazy-Container'))[0] === '#' . $this->getRealId();
    }

    /** @inheritdoc */
    public function run()
    {
        if ($this->isCurrentLazyRequest()) {
            $this->view->endBody();
            $this->view->endPage(true);
        }

        $content = trim(ob_get_clean());

        if ($this->content === '' && $content !== '') {
            $this->content = $content;
        }

        if (!$this->isCurrentLazyRequest()) {
            $this->registerAssets();
            echo Html::tag('div', $this->content, $this->options);
            return;
        }

        $response = Yii::$app->response;
        $response->format = 'lazy';
        $response->clearOutputBuffers();
        $response->setStatusCode(200);
        $response->data = $this;
        $response->send();

        Yii::$app->end();
    }

    public function registerAssets()
    {
        LazyAsset::register($this->view);

        $type = $this->type === self::TYPE_FORM ? 'lazyForm' : 'lazyContainer';

        $this->view->registerJs("$('#{$this->options['id']}').{$type}(" . Json::encode($this->jsOptions) . ")");
    }
}