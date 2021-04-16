<?php namespace modules\account\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\Permission;
use modules\account\web\admin\Controller;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class PermissionController extends Controller
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
                'actions' => ['index','set-access'],
                'roles' => ['admin.staff.role.permission'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @param string $role
     *
     * @return array
     */
    public function actionIndex($role)
    {
        $permissionTree = Permission::tree($role);

        Yii::$app->response->format = Response::FORMAT_JSON;

        return $permissionTree;
    }

    /**
     * @param string $name
     * @param string $role
     * @param bool|string|int $access
     *
     * @return array
     * @throws Throwable
     */
    public function actionSetAccess($name, $role, $access)
    {
        $model = Permission::toModel($name);
        $access = boolval(intval($access));

        Yii::$app->response->format = Response::FORMAT_JSON;

        $action = $model->toggle($role);


        if($action){
            return [
                'success' => true,
                'model' => $model,
                'messages' => [
                    'success' => [
                        Yii::t('app', '{object} ({object_name}) successfully set', [
                            'object' => Yii::t('app', 'Permission'),
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
                    Yii::t('app', 'Failed to set {object}', [
                        'object' => Yii::t('app', 'Permission'),
                    ]),
                ],
            ],
        ];
    }
}
