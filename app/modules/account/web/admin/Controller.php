<?php namespace modules\account\web\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\web\Controller as BaseController;
use modules\ui\widgets\lazy\Lazy;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property View $view
 */
class Controller extends BaseController
{
    public $layout = '@modules/account/views/layouts/admin/main';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Lazy::isLazyRequest()) {
            Yii::$app->response->format = 'lazy';
        }

        return true;
    }

    /**
     * @inheritdoc
     *
     * TODO: Need better solution for lazy load in layout
     */
    public function render($view, $params = [])
    {
        if ($this->layout !== '@modules/account/views/layouts/admin/main') {
            return parent::render($view, $params);
        }

        ob_start();
        ob_implicit_flush(false);

        Lazy::begin([
            'id' => 'main-container',
            'options' => [
                'id' => 'main-container',
            ],
            'jsOptions' => [
                'pushState' => true,
                'main' => true,
            ],
        ]);

        $content = $this->getView()->render($view, $params, $this);

        echo $this->getView()->render('@modules/account/views/layouts/admin/components/main', compact('content'));

        Lazy::end();

        return $this->renderContent(ob_get_clean());
    }

    /**
     * @param string|null  $message
     * @param array|string $redirect
     *
     * @return string|Response|array
     */
    public function notFound($message = null, $redirect = ['/'])
    {
        Yii::$app->response->statusCode = 404;

        if ($message) {
            Yii::$app->response->statusText = $message;
        }

        if (Lazy::isLazyModalRequest()) {
            Lazy::close();

            return '';
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'success' => false,
                'messages' => Yii::$app->session->getAllFlashes(),
            ];
        }

        return $this->goBack($redirect);
    }
}
