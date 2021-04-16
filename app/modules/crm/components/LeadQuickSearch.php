<?php namespace modules\crm\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\View;
use modules\crm\models\forms\lead\LeadSearch;
use modules\quick_access\components\QuickSearch;
use Yii;
use yii\data\BaseDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class LeadQuickSearch extends QuickSearch
{

    /**
     * @return string
     */
    public function getLabel()
    {
        return Yii::t('app', 'Lead');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'lead';
    }

    /**
     * @inheritDoc
     */
    public function isActive()
    {
        return Yii::$app->user->can('admin.lead.list');
    }

    /**
     * @param $q
     *
     * @return BaseDataProvider
     */
    public function search($q)
    {
        $searchModel = new LeadSearch();

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
        return $view->render('@modules/crm/views/admin/lead/components/quick-search-result-item', compact('model'));
    }
}
