<?php namespace modules\core;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use app\modules\account\web\admin\Application as AdminApplication;
use modules\account\web\admin\Controller;
use modules\account\web\admin\View;
use modules\core\base\Module;
use modules\core\components\AdminHook;
use modules\core\components\Hook;
use modules\core\components\SettingObject;
use modules\core\components\SettingRenderer;
use modules\ui\widgets\lazy\LazyResponse;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\data\Pagination;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Core extends Module implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if (isset($app->params['isRest']) && $app->params['isRest']) {
            $this->registerRestRoute($app);

            Yii::$container->set(Pagination::class, [
                'pageSizeParam' => 'page_size',
            ]);
        }
    }

    /**
     * @param Application $app
     */
    protected function registerRestRoute($app)
    {

        $app->getUrlManager()->addRules([
            "<version>/<module>" => "/<module>/rest/<version>/default/index",
            "<version>/<module>/<controller>" => "/<module>/rest/<version>/<controller>",
            "<version>/<module>/<controller>/<action>" => "/<module>/rest/<version>/<controller>/<action>",
        ]);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (Yii::$app->response instanceof Response) {
            Yii::$app->response->formatters['lazy'] = [
                'class' => LazyResponse::class,
            ];
        }

        Hook::instance();
    }
}