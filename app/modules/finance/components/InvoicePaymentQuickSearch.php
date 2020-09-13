<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\View;
use modules\finance\models\forms\invoice\InvoiceSearch;
use modules\finance\models\forms\invoice_payment\InvoicePaymentSearch;
use modules\finance\models\Invoice;
use modules\finance\models\InvoicePayment;
use modules\quick_access\components\QuickSearch;
use Yii;
use yii\data\BaseDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class InvoicePaymentQuickSearch extends QuickSearch
{

    /**
     * @return string
     */
    public function getLabel()
    {
        return Yii::t('app', 'Payment');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'invoice_payment';
    }

    /**
     * @param $q
     *
     * @return BaseDataProvider
     */
    public function search($q)
    {
        $searchModel = new InvoicePaymentSearch();

        $searchModel->apply(compact('q'), '');

        return $searchModel->dataProvider;
    }

    /**
     * @param InvoicePayment $model
     * @param View    $view
     *
     * @return string
     */
    public function render($model, $view)
    {
        return $view->render('@modules/finance/views/admin/invoice-payment/components/quick-search-result-item', compact('model'));
    }
}