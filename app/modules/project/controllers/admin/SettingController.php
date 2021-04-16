<?php namespace modules\project\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\Controller;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class SettingController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['index'],
                'roles' => ['@'],
            ],
        ];

        return $behaviors;
    }

    public $menu = [
        'general' => [
            'route' => ['/core/admin/setting/index', 'section' => 'project'],
            'role' => 'admin.setting.task.general',
        ],
        'project-status' => [
            'route' => ['/task/admin/project-status/index'],
            'role' => 'admin.setting.project.project-status.list',
        ],
    ];

    public function actionIndex()
    {
        foreach ($this->menu AS $item) {
            if (!Yii::$app->user->can($item['role'])) {
                continue;
            }

            return $this->redirect($item['route']);
        }

        return $this->redirect(['/']);
    }
}
