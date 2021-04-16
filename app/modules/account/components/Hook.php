<?php namespace modules\account\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use app\modules\account\web\admin\Application as AdminApplication;
use modules\account\models\Account as AccountModel;
use modules\account\models\AccountSession;
use modules\core\components\HookTrait;
use modules\core\components\SettingRenderer;
use modules\core\controllers\admin\SettingController;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\web\User;
use yii\web\UserEvent;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Hook
{
    use HookTrait;

    protected function __construct()
    {
        Event::on(SettingRenderer::class, SettingRenderer::EVENT_INIT, [$this, 'registerSetting']);

        Event::on(User::class, User::EVENT_AFTER_LOGIN, [$this, 'afterLogin']);
        Event::on(User::class, User::EVENT_BEFORE_LOGOUT, [$this, 'beforeLogout']);

        if (isset(Yii::$app->user)) {
            $this->setCurrentAccountToActive();
        }

        if (Yii::$app instanceof AdminApplication) {
            AdminHook::instance();
        }
    }


    /**
     * @return bool
     */
    protected function setCurrentAccountToActive()
    {
        if (!Yii::$app->setting->get('is_session_log_enabled') || Yii::$app->user->isGuest) {
            return true;
        }

        /** @var AccountModel $account */
        $account = Yii::$app->user->identity;

        return $account->setActive();
    }

    /**
     * @param Event $event
     *
     * @throws InvalidConfigException
     */
    public function registerSetting($event)
    {
        /** @var SettingRenderer $renderer */
        $renderer = $event->sender;

        $renderer->addObject(SettingObject::class);
    }

    /**
     * @param UserEvent $event
     *
     * @return bool|void
     * @throws InvalidConfigException
     */
    public function beforeLogout($event)
    {
        if (!Yii::$app->setting->get('is_session_log_enabled')) {
            return;
        }

        /** @var User $user */
        $user = $event->sender;

        if (!$user->enableSession) {
            return;
        }

        $sessionId = Yii::$app->getSession()->getId();

        if (!$sessionId) {
            return;
        }

        $model = AccountSession::find()->andWhere([
            'session_id' => $sessionId,
            'logged_out_at' => null,
            'account_id' => $event->identity->getId(),
        ])->one();

        if (!$model) {
            return;
        }

        $model->logged_out_at = time();

        return $model->save();
    }

    /**
     * @param UserEvent $event
     *
     * @throws InvalidConfigException
     */
    public function afterLogin($event)
    {
        if (!Yii::$app->setting->get('is_session_log_enabled')) {
            return;
        }

        /** @var User $user */
        $user = $event->sender;

        if (!$user->enableSession) {
            return;
        }

        $sessionId = Yii::$app->getSession()->getId();

        if (!$sessionId) {
            return;
        }

        $model = AccountSession::find()->andWhere([
            'session_id' => $sessionId,
            'logged_out_at' => null,
            'account_id' => $user->getId(),
        ])->one();

        if (!$model) {
            $model = new AccountSession([
                'session_id' => $sessionId,
                'logged_in_at' => time(),
                'account_id' => $event->identity->getId(),
            ]);
        }

        $model->ip = Yii::$app->getRequest()->getUserIP();
        $model->user_agent = Yii::$app->getRequest()->getUserAgent();
        $model->last_activity_at = time();

        $model->save();
    }
}
