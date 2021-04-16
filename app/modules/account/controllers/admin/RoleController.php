<?php namespace modules\account\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\Role;
use modules\account\web\admin\Controller;
use Yii;
use yii\web\Response;
use function compact;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class RoleController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['index'],
                'verbs' => ['GET'],
                'roles' => ['admin.staff.role.list'],
            ],
            [
                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.staff.role.add'],
                'matchCallback' => function () {
                    return (bool) (int) Yii::$app->request->post('is_new', true);
                },
            ],
            [
                'allow' => true,
                'actions' => ['update'],
                'verbs' => ['GET', 'POST', 'PATCH'],
                'roles' => ['admin.staff.role.update'],
                'matchCallback' => function () {
                    return !((bool) (int) Yii::$app->request->post('is_new', true));
                },
            ],
            [
                'allow' => true,
                'actions' => ['delete'],
                'verbs' => ['DELETE', 'POST'],
                'roles' => ['admin.staff.role.delete'],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $tree = Role::tree();
        $permissionTree = Role::tree();

        return $this->render('index', compact('tree', 'permissionTree'));
    }

    public function actionUpdate()
    {
        $data = Yii::$app->request->post();
        $model = $data['is_new'] ? new Role() : Role::find($data['name']);

        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($model->load($data, '') && $model->save()) {
            return [
                'success' => true,
                'model' => $model,
                'messages' => [
                    'success' => [
                        Yii::t('app', '{object} ({object_name}) successfully saved', [
                            'object' => Yii::t('app', 'Role'),
                            'object_name' => $model->description,
                        ]),
                    ],
                ],
            ];
        }

        return [
            'success' => false,
            'messages' => [
                'danger' => [
                    Yii::t('app', 'Failed to save {object}', [
                        'object' => Yii::t('app', 'Role'),
                    ]),
                ],
            ],
        ];
    }

    public function actionDelete($name)
    {
        $model = Role::find($name);

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$model) {
            return $this->notFound();
        }

        if (($isDeleted = $model->delete())) {
            $messages = [
                'success' => [
                    Yii::t('app', '{object} ({object_name}) successfully deleted', [
                        'object' => Yii::t('app', 'Role'),
                        'object_name' => $model->description,
                    ]),
                ],
            ];
        } else {
            $messages = [
                'danger' => [
                    Yii::t('app', 'Failed to delete {object}', [
                        'object' => Yii::t('app', 'Role'),
                    ]),
                ],
            ];
        }

        return [
            'success' => $isDeleted,
            'messages' => $messages,
        ];
    }
}
