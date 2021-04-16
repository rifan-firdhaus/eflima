<?php namespace modules\task\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\Controller;
use modules\core\helpers\Common;
use modules\task\models\query\TaskChecklistQuery;
use modules\task\models\Task;
use modules\task\models\TaskChecklist;
use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;
use function strpos;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskChecklistController extends Controller
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
                'actions' => ['change'],
                'verbs' => ['POST'],
                'roles' => ['admin.task.checklist.add'],
                'matchCallback' => function () {
                    $id = Yii::$app->request->post('id');

                    return strpos($id, '__') === 0;
                },
            ],
            [
                'allow' => true,
                'actions' => ['change'],
                'verbs' => ['POST', 'DELETE'],
                'roles' => ['admin.task.checklist.delete'],
                'matchCallback' => function () {
                    return empty(Yii::$app->request->post('label'));
                },
            ],
            [
                'allow' => true,
                'actions' => ['change'],
                'verbs' => ['POST'],
                'roles' => ['admin.task.checklist.update'],
                'matchCallback' => function () {
                    return $this->isUpdateRequest();
                },
            ],
            [
                'allow' => true,
                'actions' => ['change'],
                'verbs' => ['POST'],
                'roles' => ['admin.task.checklist.toggle'],
                'matchCallback' => function () {
                    return !$this->isUpdateRequest();
                },
            ],
            [
                'allow' => true,
                'actions' => ['sort'],
                'verbs' => ['POST'],
                'roles' => ['admin.task.checklist.update'],
            ],
        ];

        return $behaviors;
    }

    protected function isUpdateRequest()
    {
        $id = Yii::$app->request->post('id');
        $taskId = Yii::$app->request->get('task_id');
        $label = Yii::$app->request->post('label');

        if (strpos($id, '__') === 0 || empty($label)) {
            return false;
        }

        $model = TaskChecklist::find()->andWhere(['id' => $id, 'task_id' => $taskId])->one();
        $model->load(Yii::$app->request->post(), '');

        return !$model->isAttributeChanged('is_checked');
    }

    public function actionChange($task_id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('It is only served ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');

        if (!$id) {
            throw new InvalidConfigException("Parameter id is required");
        }

        /** @var TaskChecklist $model */

        if (strpos($id, '__') === 0) {
            $model = new TaskChecklist([
                'task_id' => $task_id,
                'scenario' => 'admin/add',
            ]);
        } else {
            $model = TaskChecklist::find()->andWhere(['id' => $id, 'task_id' => $task_id])->one();
            $model->scenario = 'admin/update';
        }

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Checkbox List'),
            ]));
        }

        if ($model->load(Yii::$app->request->post(), '')) {
            if (Common::isEmpty($model->label) && !$model->isNewRecord) {
                if ($model->delete()) {
                    return [
                        'success' => true,
                        'messages' => [
                            'success' => [
                                Yii::t('app', '{object} successfully deleted', ['object' => Yii::t('app', 'Checklist')]),
                            ],
                        ],
                        'id' => $model->id,
                    ];
                }
            } elseif ($model->save()) {
                return [
                    'success' => true,
                    'messages' => [
                        'success' => [
                            Yii::t('app', '{object} successfully saved', ['object' => Yii::t('app', 'Checklist')]),
                        ],
                    ],
                    'id' => $model->id,
                ];
            }
        }

        return [
            'success' => false,
            'messages' => [
                'danger' => [
                    Yii::t('app', 'Failed to save {object}', [
                        'object' => Yii::t('app', 'Checklist'),
                    ]),
                ],
            ],
        ];
    }

    public function actionSort($task_id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('It is only served ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new DynamicModel([
            'sort' => Yii::$app->request->post('sort'),
            'task_id' => $task_id,
        ]);

        $model->addRule(['sort', 'task_id'], 'required')
            ->addRule('sort', 'exist', [
                'allowArray' => true,
                'targetClass' => TaskChecklist::class,
                'targetAttribute' => 'id',
                'filter' => function ($query) use ($task_id) {
                    /** @var TaskChecklistQuery $query */

                    return $query->andWhere(['task_id' => $task_id]);
                },
            ])
            ->addRule('task_id', 'exist', [
                'targetClass' => Task::class,
                'targetAttribute' => 'id',
            ]);

        if ($model->validate() && TaskChecklist::sort($task_id, $model->sort)) {
            return [
                'successs' => true,
            ];
        }

        return [
            'success' => false,
            'messages' => [
                'danger' => [
                    Yii::t('app', 'Failed to sort {object}', [
                        'object' => Yii::t('app', 'Checklist'),
                    ]),
                ],
            ],
        ];
    }
}
