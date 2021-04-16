<?php namespace modules\task\controllers\admin;

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
            'route' => ['/core/admin/setting/index', 'section' => 'task'],
            'role' => 'admin.setting.task.general',
        ],
        'task-status' => [
            'route' => ['/task/admin/task-status/index'],
            'role' => 'admin.setting.task.task-status.list',
        ],
        'task-priority' => [
            'route' => ['/task/admin/task-priority/index'],
            'role' => 'admin.setting.task.task-priority.list',
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
