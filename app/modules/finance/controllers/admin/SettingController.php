<?php namespace modules\finance\controllers\admin;

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
            'route' => ['/core/admin/setting/index', 'section' => 'finance'],
            'role' => 'admin.setting.finance.general',
        ],
        'currency' => [
            'route' => ['/finance/admin/currency/index'],
            'role' => 'admin.setting.finance.currency.list',
        ],
        'tax' => [
            'route' => ['/finance/admin/tax/index'],
            'role' => 'admin.setting.finance.tax.list',
        ],
        'expense-category' => [
            'route' => ['/finance/admin/expense-category/index'],
            'role' => 'admin.setting.finance.expense-category.list',
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
