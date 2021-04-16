<?php namespace modules\account\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\AccountNotification;
use modules\account\models\StaffAccount;
use modules\account\models\forms\account_notification\AccountNotificationSearch;
use modules\account\web\admin\Controller;
use modules\ui\widgets\form\Form;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception as DbException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class NotificationController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [

                'allow' => true,
                'actions' => ['index','visit'],
                'roles' => ['@'],
                'verbs' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @return array|string
     * @throws Throwable
     * @throws DbException
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new AccountNotificationSearch();

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $searchModel->getQuery()->to($account);

        $dataProvider = $searchModel->apply($params);

        $result = $this->renderPartial('index', compact('searchModel', 'dataProvider'));

        AccountNotification::seenAll($dataProvider->models, $account);

        return $result;
    }

    /**
     * @param $id
     *
     * @return string|Response
     * @throws InvalidConfigException
     */
    public function actionVisit($id)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $model = $this->getModel($id);

        if (!($model instanceof AccountNotification)) {
            return $model;
        }

        $model->read($account);

        $url = $model->getRenderedUrl();

        if (!$url) {
            return $this->goBack(['/']);
        }

        return $this->redirect($url);
    }

    /**
     * @param integer                    $id
     * @param string|AccountNotification $modelClass
     * @param null|Closure               $queryFilter
     *
     * @return string|Response|AccountNotification
     *
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = AccountNotification::class, $queryFilter = null)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $query = $modelClass::find()->to($account)->andWhere(['account_notification.id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Notification'),
            ]));
        }

        return $model;
    }
}
