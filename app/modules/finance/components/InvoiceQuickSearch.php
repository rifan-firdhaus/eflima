<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\View;
use modules\finance\models\forms\invoice\InvoiceSearch;
use modules\finance\models\Invoice;
use modules\quick_access\components\QuickSearch;
use Yii;
use yii\data\BaseDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class InvoiceQuickSearch extends QuickSearch
{

    /**
     * @return string
     */
    public function getLabel()
    {
        return Yii::t('app', 'Invoice');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'invoice';
    }

    /**
     * @param $q
     *
     * @return BaseDataProvider
     */
    public function search($q)
    {
        $searchModel = new InvoiceSearch();

        $searchModel->apply(compact('q'), '');

        return $searchModel->dataProvider;
    }

    /**
     * @param Invoice $model
     * @param View    $view
     *
     * @return string
     */
    public function render($model, $view)
    {
        return $view->render('@modules/finance/views/admin/invoice/components/quick-search-result-item', compact('model'));
    }
}