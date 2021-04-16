<?php namespace modules\support\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\crm\models\Customer;
use modules\file_manager\web\UploadedFile;
use modules\support\models\forms\ticket\TicketSearch;
use modules\support\models\forms\ticket_reply\TicketReplySearch;
use modules\support\models\Ticket;
use modules\support\models\TicketReply;
use modules\task\models\forms\task\TaskSearch;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TicketController extends Controller
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
                'actions' => ['index'],
                'verbs' => ['GET'],
                'roles' => ['admin.ticket.list'],
            ],
            [
                'allow' => true,
                'actions' => ['add'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.ticket.add'],
            ],
            [
                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.ticket.update'],
            ],
            [
                'allow' => true,
                'actions' => ['view'],
                'verbs' => ['GET'],
                'roles' => ['admin.ticket.view.detail'],
            ],
            [
                'allow' => true,
                'actions' => ['task'],
                'verbs' => ['GET'],
                'roles' => ['admin.ticket.view.task'],
            ],
            [
                'allow' => true,
                'actions' => ['history'],
                'verbs' => ['GET'],
                'roles' => ['admin.ticket.view.history'],
            ],
            [
                'allow' => true,
                'actions' => ['delete'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.ticket.delete'],
            ],
            [
                'allow' => true,
                'actions' => ['change-status'],
                'verbs' => ['POST'],
                'roles' => ['admin.ticket.status'],
            ],
            [
                'allow' => true,
                'actions' => ['change-priority'],
                'verbs' => ['POST'],
                'roles' => ['admin.ticket.status'],
            ],
            [
                'allow' => true,
                'actions' => ['auto-complete'],
                'verbs' => ['GET'],
                'roles' => ['@'],
            ],
        ];

        return $behaviors;
    }
    /**
     * @param string $view
     *
     * @return array|string|Response
     *
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionIndex($view = 'default')
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new TicketSearch();

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        $searchModel->apply($params);

        switch ($view) {
            case 'customer':
                $customerId = Yii::$app->request->get('customer_id');

                if (!$customerId) {
                    throw new BadRequestHttpException('Missing required parameter: customer_id');
                }

                return $this->indexOfCustomer($customerId, $searchModel);
        }

        return $this->render('index', compact('searchModel'));
    }

    /**
     * @param int|string   $customerId
     * @param TicketSearch $searchModel
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function indexOfCustomer($customerId, $searchModel)
    {
        $customer = Customer::find()->andWhere(['id' => $customerId])->one();

        if (!$customer) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Customer'),
            ]));
        }

        $searchModel->getQuery()->joinWith('contact')->andWhere(['contact_of_ticket.customer_id' => $customerId]);

        $searchModel->params['customer_id'] = $customerId;

        return $this->render('index-customer', compact('searchModel', 'customer'));
    }

    /**
     * @param Ticket           $model
     * @param                  $data
     *
     * @return string|array
     */
    protected function modify($model, $data)
    {
        $model->loadDefaultValues();

        if ($model->load($data)) {
            $model->uploaded_attachments = UploadedFile::getInstances($model, 'uploaded_attachments');

            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($model);
            }

            if ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully saved', [
                    'object' => Yii::t('app', 'Ticket'),
                    'object_name' => $model->subject,
                ]));

                if (Lazy::isLazyModalRequest() || Lazy::isLazyInsideModalRequest()) {
                    Lazy::close();

                    return;
                }

                return $this->redirect(['update', 'id' => $model->id]);
            } elseif ($model->hasErrors()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'ticket'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->getModel($id, Ticket::class);

        if (!($model instanceof Ticket)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param        $id
     * @param string $action
     *
     * @return Ticket|string|Response
     * @throws InvalidConfigException
     */
    public function actionView($id, $action = 'default')
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;
        $model = $this->getModel($id);

        if (!($model instanceof Ticket)) {
            return $model;
        }

        switch ($action) {
            case 'task':
                return $this->task(compact('model'));
        }

        $replyModel = new TicketReply([
            'ticket_id' => $model->id,
            'staff_id' => $account->profile->id,
            'scenario' => 'admin/reply',
        ]);

        $replyModel->loadDefaultValues();

        $replySearchModel = new TicketReplySearch();

        $replySearchModel->getQuery()->andWhere(['ticket_reply.ticket_id' => $model->id]);

        $replyDataProvider = $replySearchModel->apply(Yii::$app->request->queryParams);

        return $this->render('view', compact('model', 'replyModel', 'replySearchModel', 'replyDataProvider'));
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function task($params)
    {
        /** @var Ticket $model */
        $model = $params['model'];
        $searchModel = $params['searchModel'] = new TaskSearch([
            'params' => [
                'model' => 'ticket',
                'model_id' => $model->id,
            ],
        ]);

        $params['dataProvider'] = $searchModel->apply(Yii::$app->request->get());

        return $this->render('task', $params);
    }

    /**
     * @param int|string|null $contact_id
     *
     * @return array|string|Response
     */
    public function actionAdd($contact_id = null)
    {
        $model = new Ticket([
            'scenario' => 'admin/add',
            'contact_id' => $contact_id,
        ]);

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer       $id
     * @param string|Ticket $modelClass
     * @param null|Closure  $queryFilter
     *
     * @return string|Response|Ticket
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = Ticket::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Ticket'),
            ]));
        }

        return $model;
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Ticket)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Department'),
                'object_name' => $model->subject,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Department'),
            ]));
        }

        return $this->goBack(['index']);
    }


    /**
     * @param int|string $id
     * @param int        $status
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionChangeStatus($id, $status)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Ticket)) {
            return $model;
        }

        if ($model->changeStatus($status)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{field} of {object_name} successfully changed', [
                'field' => Yii::t('app', 'Priority'),
                'object_name' => $model->subject,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to change {field} of {object}', [
                'object' => Yii::t('app', 'Ticket'),
                'field' => Yii::t('app', 'status'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param int|string $id
     * @param int        $priority
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionChangePriority($id, $priority)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Ticket)) {
            return $model;
        }

        if ($model->changePriority($priority)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{field} of {object_name} successfully changed', [
                'field' => Yii::t('app', 'Priority'),
                'object_name' => $model->subject,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to change {field} of {object}', [
                'object' => Yii::t('app', 'Ticket'),
                'action' => Yii::t('app', 'priority'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @return array
     *
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     */
    public function actionAutoComplete()
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new TicketSearch();

        return $searchModel->autoComplete(Yii::$app->request->queryParams);
    }
}
