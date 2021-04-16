<?php namespace modules\finance\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\forms\history\HistorySearch;
use modules\account\models\queries\HistoryQuery;
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\core\helpers\Common;
use modules\crm\models\Customer;
use modules\finance\models\forms\invoice\InvoiceSearch;
use modules\finance\models\forms\invoice_payment\InvoicePaymentSearch;
use modules\finance\models\Invoice;
use modules\finance\models\InvoiceItem;
use modules\task\models\forms\task\TaskSearch;
use modules\task\models\Task;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Expression;
use yii\db\StaleObjectException;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\RangeNotSatisfiableHttpException;
use yii\web\Response;
use ZipArchive;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class InvoiceController extends Controller
{
    public $viewMenu = [
        'detail' => [
            'route' => ['/finance/admin/invoice/detail'],
            'role' => 'admin.invoice.view.detail',
        ],
        'payment' => [
            'route' => ['/finance/admin/invoice/payment'],
            'role' => 'admin.invoice.view.payment',
        ],
        'task' => [
            'route' => ['/finance/admin/invoice/task'],
            'role' => 'admin.invoice.view.task',
        ],
        'history' => [
            'route' => ['/finance/admin/invoice/history'],
            'role' => 'admin.invoice.view.history',
        ],
    ];

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
                'roles' => ['admin.invoice.list'],
                'matchCallback' => function () {
                    return Yii::$app->request->get('view', 'default') === 'default';
                },
            ],
            [
                'allow' => true,
                'actions' => ['index'],
                'verbs' => ['GET'],
                'roles' => ['admin.customer.view.invoice'],
                'matchCallback' => function () {
                    return Yii::$app->request->get('view', 'default') === 'customer';
                },
            ],
            [
                'allow' => true,
                'actions' => ['add'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.invoice.add'],
            ],
            [
                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.invoice.update'],
            ],
            [
                'allow' => true,
                'actions' => ['delete', 'bulk-delete'],
                'verbs' => ['DELETE', 'POST'],
                'roles' => ['admin.invoice.delete'],
            ],
            [
                'allow' => true,
                'actions' => ['detail', 'download', 'bulk-download'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.invoice.view.detail'],
            ],
            [
                'allow' => true,
                'actions' => ['payment'],
                'verbs' => ['GET'],
                'roles' => ['admin.invoice.view.payment'],
            ],
            [
                'allow' => true,
                'actions' => ['task'],
                'verbs' => ['GET'],
                'roles' => ['admin.invoice.view.task'],
            ],
            [
                'allow' => true,
                'actions' => ['history'],
                'verbs' => ['GET'],
                'roles' => ['admin.invoice.view.history'],
            ],
            [
                'allow' => true,
                'actions' => ['auto-complete', 'view'],
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

        $searchModel = new InvoiceSearch();

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
     * @param int|string    $customerId
     * @param InvoiceSearch $searchModel
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

        $searchModel->getQuery()->andWhere(['invoice.customer_id' => $customerId]);

        $searchModel->params['customer_id'] = $customerId;

        return $this->render('index-customer', compact('searchModel', 'customer'));
    }

    /**
     * @param string|int $id
     *
     * @return Response
     */
    public function actionView($id)
    {
        foreach ($this->viewMenu AS $item) {
            if (!Yii::$app->user->can($item['role'])) {
                continue;
            }

            $route = $item['route'];

            if (is_callable($route)) {
                call_user_func($route, $id);
            } else {
                $route['id'] = $id;
            }


            return $this->redirect($route);
        }

        return $this->redirect(['/']);
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionDetail($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Invoice)) {
            return $model;
        }

        return $this->render('view', compact('model'));
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionPayment($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Invoice)) {
            return $model;
        }

        $paymentSearchModel = new InvoicePaymentSearch([
            'params' => [
                'invoice_id' => $model->id,
            ],
        ]);

        $paymentSearchModel->getQuery()->andWhere(['invoice_payment.invoice_id' => $model->id]);

        $paymentSearchModel->apply(Yii::$app->request->get());

        return $this->render('payment', compact('paymentSearchModel', 'model'));
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionTask($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Invoice)) {
            return $model;
        }

        $taskSearchModel = new TaskSearch([
            'params' => [
                'model' => 'invoice',
                'model_id' => $model->id,
            ],
        ]);

        $taskSearchModel->apply(Yii::$app->request->get());

        return $this->render('task', compact('taskSearchModel', 'model'));
    }

    /**
     * @param string|int $id
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionHistory($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Invoice)) {
            return $model;
        }

        $historySearchParams = [
            'model' => Invoice::class,
            'model_id' => $model->id,
            'models' => [],
        ];


        if (Yii::$app->hasModule('task')) {
            $historySearchParams['models'][] = function ($query) use ($model) {
                /** @var HistoryQuery $query */

                $query->leftJoin(Task::tableName(), [
                    'task.id' => new Expression('[[history.model_id]]'),
                    'history.model' => Task::class,
                    'task.model_id' => $model->id,
                    'task.model' => 'invoice',
                ]);

                return ['IS NOT', 'task.id', null];
            };
        }

        $historySearchModel = new HistorySearch([
            'params' => $historySearchParams,
        ]);


        return $this->render('history', compact('historySearchModel', 'model'));
    }

    /**
     * @param Invoice    $model
     * @param            $data
     *
     * @return string|array
     *
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    protected function modify($model, $data)
    {
        $model->loadDefaultValues();

        if ($model->load($data)) {

            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($model);
            }

            $itemData = Json::decode(Yii::$app->request->post('items'));
            $model->itemModels = $this->getItemModels($itemData, $model);

            if (!$model->validate() || !Model::validateMultiple($model->itemModels)) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } elseif ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully saved', [
                    'object' => Yii::t('app', 'Invoice'),
                    'object_name' => $model->number,
                ]));

                if (Lazy::isLazyModalRequest() || Lazy::isLazyInsideModalRequest()) {
                    Lazy::close();

                    return;
                }

                return $this->redirect(['update', 'id' => $model->id]);
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'Invoice'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @param array   $data
     * @param Invoice $invoice
     *
     * @return array
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function getItemModels($data, $invoice)
    {
        $models = [];

        foreach ($data AS $item) {
            if (!empty($item['id']) && !$invoice->isNewRecord) {
                $model = InvoiceItem::find()->andWhere(['id' => $item['id'], 'invoice_id' => $invoice->id])->one();

                if (!$model) {
                    throw new NotFoundHttpException('Can\'t find invoice item');
                }

                $model->scenario = 'admin/update';
            } else {
                $model = new InvoiceItem([
                    'scenario' => 'admin/add',
                ]);
            }

            $model->loadDefaultValues();
            $model->setAttributes($item);

            $models[] = $model;
        }

        return $models;
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $model = $this->getModel($id, Invoice::class);

        if (!($model instanceof Invoice)) {
            return $model;
        }

        $model->scenario = 'admin/update';
        $model->assignor_id = $account->profile->id;

        $model->normalizeAttributes();

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param null|string|int $customer_id
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionAdd($customer_id = null)
    {
        /** @var StaffAccount $account */
        $account = Yii::$app->user->identity;

        $model = new Invoice([
            'scenario' => 'admin/add',
            'customer_id' => $customer_id,
            'assignor_id' => $account->profile->id,
        ]);

        if (!Common::isEmpty($customer_id) && !$model->getCustomer()->exists()) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Customer'),
            ]));
        }

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer        $id
     * @param string|Invoice $modelClass
     * @param null|Closure   $queryFilter
     *
     * @return string|Response|Invoice
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = Invoice::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Invoice'),
            ]));
        }

        return $model;
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Invoice)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Invoice'),
                'object_name' => $model->number,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Invoice'),
            ]));
        }

        return $this->goBack(['index']);
    }


    /**
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     * @throws Throwable
     *
     */
    public function actionBulkDelete()
    {
        $ids = (array) Yii::$app->request->post('id', []);

        $total = Invoice::find()->andWhere(['id' => $ids])->count();

        if (count($ids) < $total) {
            return $this->notFound(Yii::t('app', 'Some {object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Invoices'),
            ]));
        }

        if (Invoice::bulkDelete($ids)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{number} {object} successfully deleted', [
                'number' => count($ids),
                'object' => Yii::t('app', 'Invoices'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param      $id
     * @param bool $inline
     *
     * @throws InvalidConfigException
     * @throws MpdfException
     * @throws RangeNotSatisfiableHttpException
     */
    public function actionDownload($id, $inline = false)
    {
        $model = $this->getModel($id);
        $pdf = $model->asPDF();
        $inline = (bool) (int) $inline;

        $fileName = Yii::t('app', 'Invoice #{number}', ['number' => $model->number]) . '.pdf';

        $output = $pdf->Output($fileName, Destination::STRING_RETURN);

        Yii::$app->response->sendContentAsFile($output, $fileName, [
            'inline' => $inline,
            'mimeType' => 'application/pdf',
        ]);
    }

    /**
     * @throws InvalidConfigException
     * @throws MpdfException
     */
    public function actionBulkDownload()
    {
        $ids = Yii::$app->request->get('id', '');
        $ids = explode(',', $ids);
        $dir = Yii::getAlias('@runtime/invoice');

        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $fileName = $dir . '/' . time() . '-' . rand(0, 100000) . '.zip';

        $zip = new ZipArchive();
        $zip->open($fileName, ZipArchive::CREATE);

        $query = Invoice::find()->where(['id' => $ids]);

        foreach ($query->each(10) AS $invoice) {
            /** @var Invoice $invoice */

            $pdfName = Yii::t('app', 'Invoice #{number}', ['number' => $invoice->number]) . '.pdf';
            $pdf = $invoice->asPDF();

            $zip->addFromString($pdfName, $pdf->Output($pdfName, Destination::STRING_RETURN));
        }

        $zip->close();

        Yii::$app->response->sendFile(Yii::getAlias('@webroot/public/inv.zip'), 'Invoices.zip', [
            'mimeType' => 'application/zip',
        ]);

        unlink($fileName);
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

        $searchModel = new InvoiceSearch();

        return $searchModel->autoComplete(Yii::$app->request->queryParams);
    }
}
