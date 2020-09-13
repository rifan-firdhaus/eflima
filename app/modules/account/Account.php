<?php namespace modules\account;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use app\modules\account\web\admin\Application as AdminApplication;
use modules\account\components\History;
use modules\account\components\Hook;
use modules\account\components\StaffQuickSearch;
use modules\account\widgets\lazy\LazyResponse;
use modules\core\base\Module;
use modules\quick_access\components\QuickSearch;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Account extends Module implements BootstrapInterface
{
    /** @var History */
    protected static $history;

    /**
     * @return History
     */
    public static function history()
    {
        if (!self::$history) {
            self::$history = new History();
        }

        return self::$history;
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if (Yii::$app instanceof AdminApplication) {
            $this->registerAdminRoute($app);

            Yii::$app->response->formatters['lazy'] = [
                'class' => LazyResponse::class,
            ];
        }
    }

    /**
     * @param Application $app
     */
    protected function registerAdminRoute($app)
    {
        $app->getUrlManager()->addRules([
            '/' => "/account/admin/staff/dashboard",
            "<module>" => "/<module>/admin/default/index",
            "<module>/<controller>" => "/<module>/admin/<controller>",
            "<module>/<controller>/<action>" => "/<module>/admin/<controller>/<action>",
        ]);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Hook::instance();

        if (Yii::$app->hasModule('quick_access')) {
            QuickSearch::register(StaffQuickSearch::class);
        }
    }

}