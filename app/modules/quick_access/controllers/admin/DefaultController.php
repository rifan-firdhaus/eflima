<?php namespace modules\quick_access\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\Controller;
use modules\quick_access\components\QuickSearch;
use Yii;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class DefaultController extends Controller
{
    public function actionQuickAdd()
    {
        return $this->renderPartial('quick-add');
    }

    public function actionQuickSearch($q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return QuickSearch::run($q, $this->view);
    }
}