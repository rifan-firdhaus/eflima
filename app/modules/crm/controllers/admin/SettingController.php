<?php namespace modules\crm\controllers\admin;

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
        [
            'route' => ['/core/admin/setting/index', 'section' => 'crm'],
            'role' => 'admin.setting.crm.general',
        ],
        [
            'route' => ['/crm/admin/customer-group/index'],
            'role' => 'admin.setting.crm.customer-group.list',
        ],
        [
            'route' => ['/crm/admin/lead-source/index'],
            'role' => 'admin.setting.crm.lead-source.list',
        ],
        [
            'route' => ['/crm/admin/lead-status/index'],
            'role' => 'admin.setting.crm.lead-status.list',
        ],
        [
            'route' => ['/crm/admin/lead-follow-up-type/index'],
            'role' => 'admin.setting.crm.lead-follow-up-type.list',
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
