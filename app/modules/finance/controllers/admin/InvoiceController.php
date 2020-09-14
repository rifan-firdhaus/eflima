<?php namespace modules\finance\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\forms\history\HistorySearch;
use modules\account\models\queries\HistoryQuery;
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
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class InvoiceController extends Controller
{
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
     * @param string     $action
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function actionView($id, $action = 'view')
    {
        $model = $this->getModel($id);

        if (!($model instanceof Invoice)) {
            return $model;
        }

        switch ($action) {
            case 'payment':
                return $this->payment($model);
            case 'history':
                return $this->history($model);
            case 'task':
                return $this->task($model);
        }

        return $this->render('view', compact('model'));
    }

    /**
     * @param Invoice $model
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    public function payment($model)
    {
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
     * @param Invoice $model
     *
     * @return string
     */
    public function task($model)
    {
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
     * @param Invoice $model
     *
     * @return string
     */
    public function history($model)
    {
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
        $model = $this->getModel($id, Invoice::class);

        if (!($model instanceof Invoice)) {
            return $model;
        }

        $model->scenario = 'admin/update';

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
        $model = new Invoice([
            'scenario' => 'admin/add',
            'customer_id' => $customer_id,
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
