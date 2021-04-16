<?php namespace modules\crm\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\View;
use modules\crm\models\forms\customer\CustomerSearch;
use modules\quick_access\components\QuickSearch;
use Yii;
use yii\data\BaseDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CustomerQuickSearch extends QuickSearch
{

    /**
     * @return string
     */
    public function getLabel()
    {
        return Yii::t('app', 'Customer');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'customer';
    }

    /**
     * @inheritDoc
     */
    public function isActive()
    {
        return Yii::$app->user->can('admin.customer.list');
    }

    /**
     * @param $q
     *
     * @return BaseDataProvider
     */
    public function search($q)
    {
        $searchModel = new CustomerSearch();

        $searchModel->apply(compact('q'), '');

        return $searchModel->dataProvider;
    }

    /**
     * @param mixed $model
     * @param View  $view
     *
     * @return string
     */
    public function render($model, $view)
    {
        return $view->render('@modules/crm/views/admin/customer/components/quick-search-result-item', compact('model'));
    }
}
