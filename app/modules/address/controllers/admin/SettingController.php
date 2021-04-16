<?php namespace modules\address\controllers\admin;

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
        'country' => [
            'route' => ['/address/admin/country/index'],
            'role' => 'admin.setting.country.list',
        ],
        'province' => [
            'route' => ['/address/admin/province/index'],
            'role' => 'admin.setting.province.list',
        ],
        'city' => [
            'route' => ['/address/admin/city/index'],
            'role' => 'admin.setting.city.list',
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
