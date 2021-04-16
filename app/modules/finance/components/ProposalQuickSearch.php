<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\View;
use modules\finance\models\forms\proposal\ProposalSearch;
use modules\finance\models\Invoice;
use modules\quick_access\components\QuickSearch;
use Yii;
use yii\data\BaseDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProposalQuickSearch extends QuickSearch
{

    /**
     * @return string
     */
    public function getLabel()
    {
        return Yii::t('app', 'Proposal');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'proposal';
    }

    /**
     * @inheritDoc
     */
    public function isActive()
    {
        return Yii::$app->user->can('admin.proposal.list');
    }

    /**
     * @param $q
     *
     * @return BaseDataProvider
     */
    public function search($q)
    {
        $searchModel = new ProposalSearch();

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
        return $view->render('@modules/finance/views/admin/proposal/components/quick-search-result-item', compact('model'));
    }
}
