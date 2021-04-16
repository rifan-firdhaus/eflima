<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\View;
use modules\finance\models\forms\expense\ExpenseSearch;
use modules\quick_access\components\QuickSearch;
use Yii;
use yii\data\BaseDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ExpenseQuickSearch extends QuickSearch
{

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Expense');
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return 'expense';
    }

    /**
     * @inheritDoc
     */
    public function isActive()
    {
        return Yii::$app->user->can('admin.expense.list');
    }

    /**
     * @param $q
     *
     * @return BaseDataProvider
     */
    public function search($q)
    {
        $searchModel = new ExpenseSearch();

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
        return $view->render('@modules/finance/views/admin/expense/components/quick-search-result-item', compact('model'));
    }
}
