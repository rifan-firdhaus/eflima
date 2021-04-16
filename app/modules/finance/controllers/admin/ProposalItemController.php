<?php namespace modules\finance\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\web\admin\Controller;
use modules\account\widgets\lazy\LazyResponse;
use modules\finance\models\Proposal;
use modules\finance\models\ProposalItem;
use modules\finance\models\ProposalItemTax;
use modules\finance\models\queries\ProposalItemQuery;
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
class ProposalItemController extends Controller
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
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.proposal.item.add'],
            ],
            [

                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.proposal.item.update'],
            ],
            [

                'allow' => true,
                'actions' => ['delete'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.proposal.item.delete'],
            ],
            [

                'allow' => true,
                'actions' => ['sort'],
                'verbs' => ['POST'],
                'roles' => ['admin.proposal.item.update', 'admin.proposal.item.add'],
            ],
            [

                'allow' => true,
                'actions' => ['reevaluate'],
                'verbs' => ['POST'],
                'roles' => ['admin.proposal.item.update', 'admin.proposal.item.delete', 'admin.proposal.item.add'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @param ProposalItem $model
     * @param              $data
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
                        'model' => $model->proposal,
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
     * @param ProposalItem $model
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
            'model' => self::getProposalDummyModel($model->proposal, $itemsData),
        ]);

        return;
    }

    /**
     * @return array
     */
    public function actionReevaluate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Proposal([
            'scenario' => 'admin/temp',
        ]);
        $model->load(Yii::$app->request->post());

        $itemsData = Json::decode(Yii::$app->request->post('models'));

        self::getProposalDummyModel($model, $itemsData);

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
     * @param ProposalItem $model
     *
     * @return mixed
     */
    public static function getDummyModel($model)
    {
        $taxes = [];

        $model->normalizeAttributes(true);

        if ($model->tax_inputs) {
            foreach ($model->tax_inputs AS $taxInput) {
                $tax = new ProposalItemTax();
                $tax->proposalItem = $model;

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
     * @param Proposal $model
     * @param array    $itemsData
     *
     * @return Proposal
     */
    public static function getProposalDummyModel($model, $itemsData)
    {
        $items = [];

        foreach ($itemsData AS $key => $data) {
            $item = new ProposalItem([
                'scenario' => 'admin/temp/add',
            ]);
            $item->proposal = $model;

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
        $model = $this->getModel($id, ProposalItem::class);

        if (!($model instanceof ProposalItem)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param string|int $proposal_id
     * @param bool       $temp
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionAdd($proposal_id = null, $temp = false)
    {
        $model = new ProposalItem([
            'scenario' => intval($temp) ? 'admin/temp/add' : 'admin/add',
            'proposal_id' => $proposal_id,
        ]);

        if ($temp) {
            if ($proposal_id) {
                $model->proposal = Proposal::find()->andWhere(['id' => $proposal_id])->one();
            } else {
                $model->proposal = new Proposal([
                    'scenario' => 'admin/temp',
                ]);
            }

            $data = Json::decode(Yii::$app->request->post('model'));
            $proposalData = Json::decode(Yii::$app->request->post('proposal'));
            $model->proposal->load($proposalData, '');
            $model->load($data, '');
        }

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer             $id
     * @param string|ProposalItem $modelClass
     * @param null|Closure        $queryFilter
     *
     * @return string|Response|ProposalItem
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = ProposalItem::class, $queryFilter = null)
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

        if (!($model instanceof ProposalItem)) {
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
     * @param $proposal_id
     *
     * @return array
     * @throws MethodNotAllowedHttpException
     * @throws Throwable
     */
    public function actionSort($proposal_id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('It is only served ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new DynamicModel([
            'sort' => Yii::$app->request->post('sort'),
            'proposal_id' => $proposal_id,
        ]);

        $model->addRule(['sort', 'proposal_id'], 'required')
            ->addRule('sort', 'exist', [
                'allowArray' => true,
                'targetClass' => ProposalItem::class,
                'targetAttribute' => 'id',
                'filter' => function ($query) use ($proposal_id) {
                    /** @var ProposalItemQuery $query */

                    return $query->andWhere(['proposal_id' => $proposal_id]);
                },
            ])
            ->addRule('proposal_id', 'exist', [
                'targetClass' => Proposal::class,
                'targetAttribute' => 'id',
            ]);


        if ($model->validate() && ProposalItem::sort($proposal_id, $model->sort)) {
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
