<?php namespace modules\account\widgets\lazy;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\StaffAccount;
use modules\account\web\admin\View;
use modules\ui\widgets\lazy\Lazy;
use modules\ui\widgets\lazy\LazyResponse as BaseLazyResponse;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class LazyResponse extends BaseLazyResponse
{
    public static $lazyData = [];

    /**
     * @inheritdoc
     */
    public function formatData($response)
    {
        $data = parent::formatData($response);

        /** @var View $view */
        $view = $response->data instanceof Lazy ? $response->data->view : Yii::$app->view;

        $data['subTitle'] = $view->subTitle;
        $data['activeMenu'] = substr($view->menu->active, 5);
        $data['fullHeight'] = $view->fullHeightContent;

        if (!Yii::$app->user->isGuest) {
            /** @var StaffAccount $account */
            $account = Yii::$app->user->identity;
            $data['notificationCount'] = $account->notificationCount;
        }

        $data = ArrayHelper::merge($data, self::$lazyData);

        return $data;
    }
}