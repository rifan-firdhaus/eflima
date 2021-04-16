<?php namespace modules\finance\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\finance\models\Invoice;
use modules\finance\models\InvoiceItem;
use modules\finance\models\InvoiceItemTax;
use modules\finance\models\queries\InvoiceItemQuery;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Throwable;
use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class InvoiceItemController extends Controller
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
                'actions' => ['add'],
                'verbs' => ['GET','POST'],
                'roles' => ['admin.invoice.item.add'],
            ],
            [

                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET','POST','PATCH'],
                'roles' => ['admin.invoice.item.update'],
            ],
            [

                'allow' => true,
                'actions' => ['delete'],
                'verbs' => ['DELETE','POST'],
                'roles' => ['admin.invoice.item.delete'],
            ],
            [

                'allow' => true,
                'actions' => ['sort'],
                'verbs' => ['POST'],
                'roles' => ['admin.invoice.item.update', 'admin.invoice.item.add'],
            ],
            [

                'allow' => true,
                'actions' => ['reevaluate'],
                'verbs' => ['POST'],
                'roles' => ['admin.invoice.item.update','admin.invoice.item.delete', 'admin.invoice.item.add'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @param InvoiceItem $model
     * @param             $data
     *
     * @return string|array
     */
    protected function modify($model, $data)
    {
        $model->loadDefaultValues();

        if ($model->load($data)) {
            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($model);
            }

            if ($model->scenario === 'admin/temp/add') {
                return $this->temporarySave($model);
            }

            if ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully saved', [
                    'object' => Yii::t('app', 'Item'),
                    'object_name' => $model->name,
                ]));

                if (Lazy::isLazyModalRequest() || Lazy::isLazyInsideModalRequest()) {
                    $model->refresh();

                    Lazy::close();
                    LazyResponse::$lazyData['rows'] = [$this->renderPartial('components/item-row', compact('model'))];
                    LazyResponse::$lazyData['footer'] = $this->renderPartial('components/item-summary', [
                        'model' => $model->invoice,
                    ]);

                    return;
                }

                return $this->redirect(['update', 'id' => $model->id]);
            } elseif ($model->hasErrors()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'Item'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @param InvoiceItem $model
     *
     * @return bool|string
     */
    public function temporarySave($model)
    {
        if (!$model->validate()) {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));

            return false;
        }

        self::getDummyModel($model);

        Lazy::close();

        $itemsData = Json::decode(Yii::$app->request->post('models'));
        $itemsData[Yii::$app->request->get('temp')] = ArrayHelper::toArray($model);

        LazyResponse::$lazyData['temp'] = Yii::$app->request->get('temp');
        LazyResponse::$lazyData['rows'] = [
            Yii::$app->request->get('temp') => [
                'model' => ArrayHelper::toArray($model),
                'row' => $this->renderPartial('components/item-row', compact('model')),
            ],
        ];
        LazyResponse::$lazyData['footer'] = $this->renderPartial('components/item-summary', [
            'model' => self::getInvoiceDummyModel($model->invoice, $itemsData),
        ]);

        return;
    }

    /**
     * @return array
     */
    public function actionReevaluate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Invoice([
            'scenario' => 'admin/temp',
        ]);
        $model->load(Yii::$app->request->post());

        $itemsData = Json::decode(Yii::$app->request->post('models'));

        self::getInvoiceDummyModel($model, $itemsData);

        $result['footer'] = $this->renderPartial('components/item-summary', [
            'model' => $model,
        ]);
        $result['rows'] = [];

        foreach ($model->items AS $key => $item) {
            $result['rows'][$key] = [
                'model' => $item,
                'row' => $this->renderPartial('components/item-row', [
                    'model' => $item,
                ]),
            ];
        }

        return $result;
    }

    /**
     * @param InvoiceItem $model
     *
     * @return mixed
     */
    public static function getDummyModel($model)
    {
        $taxes = [];

        $model->normalizeAttributes(true);

        if ($model->tax_inputs) {
            foreach ($model->tax_inputs AS $taxInput) {
                $tax = new InvoiceItemTax();
                $tax->invoiceItem = $model;

                $tax->loadDefaultValues();
                $tax->load($taxInput, '');
                $tax->normalizeAttributes(true);

                $model->tax += $tax->value;

                $taxes[] = $tax;
            }
        }

        $model->taxes = $taxes;

        $model->normalizeAttributes(true);
        $model->typecastAttributes();

        return $model;
    }

    /**
     * @param Invoice $model
     * @param array   $itemsData
     *
     * @return Invoice
     */
    public static function getInvoiceDummyModel($model, $itemsData)
    {
        $items = [];

        foreach ($itemsData AS $key => $data) {
            $item = new InvoiceItem([
                'scenario' => 'admin/temp/add',
            ]);
            $item->invoice = $model;

            $item->loadDefaultValues();
            $item->load($data, '');

            self::getDummyModel($item);

            $items[$key] = $item;
        }

        $model->items = $items;

        return $model;
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->getModel($id, InvoiceItem::class);

        if (!($model instanceof InvoiceItem)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param string|int $invoice_id
     * @param bool       $temp
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionAdd($invoice_id = null, $temp = false)
    {
        $model = new InvoiceItem([
            'scenario' => intval($temp) ? 'admin/temp/add' : 'admin/add',
            'invoice_id' => $invoice_id,
        ]);

        if ($temp) {
            if ($invoice_id) {
                $model->invoice = Invoice::find()->andWhere(['id' => $invoice_id])->one();
            } else {
                $model->invoice = new Invoice([
                    'scenario' => 'admin/temp',
                ]);
            }

            $data = Json::decode(Yii::$app->request->post('model'));
            $invoiceData = Json::decode(Yii::$app->request->post('invoice'));
            $model->invoice->load($invoiceData, '');
            $model->load($data, '');
        }

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer            $id
     * @param string|InvoiceItem $modelClass
     * @param null|Closure       $queryFilter
     *
     * @return string|Response|InvoiceItem
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = InvoiceItem::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Item'),
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

        if (!($model instanceof InvoiceItem)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Item'),
                'object_name' => $model->name,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Item'),
            ]));
        }

        return $this->goBack(['index']);
    }

    /**
     * @param $invoice_id
     *
     * @return array
     * @throws MethodNotAllowedHttpException
     * @throws Throwable
     */
    public function actionSort($invoice_id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('It is only served ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new DynamicModel([
            'sort' => Yii::$app->request->post('sort'),
            'invoice_id' => $invoice_id,
        ]);

        $model->addRule(['sort', 'invoice_id'], 'required')
            ->addRule('sort', 'exist', [
                'allowArray' => true,
                'targetClass' => InvoiceItem::class,
                'targetAttribute' => 'id',
                'filter' => function ($query) use ($invoice_id) {
                    /** @var InvoiceItemQuery $query */

                    return $query->andWhere(['invoice_id' => $invoice_id]);
                },
            ])
            ->addRule('invoice_id', 'exist', [
                'targetClass' => Invoice::class,
                'targetAttribute' => 'id',
            ]);


        if ($model->validate() && InvoiceItem::sort($invoice_id, $model->sort)) {
            return [
                'successs' => true,
            ];
        }

        return [
            'success' => false,
            'messages' => [
                'danger' => [
                    Yii::t('app', 'Failed to sort {object}', [
                        'object' => Yii::t('app', 'Items'),
                    ]),
                ],
            ],
        ];
    }
}
