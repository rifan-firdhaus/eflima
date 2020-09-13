<?php namespace modules\account\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\forms\staff\StaffSearch;
use modules\account\web\admin\View;
use modules\quick_access\components\QuickSearch;
use Yii;
use yii\data\BaseDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class StaffQuickSearch extends QuickSearch
{

    /**
     * @return string
     */
    public function getLabel()
    {
        return Yii::t('app', 'Staff');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'staff';
    }

    /**
     * @param $q
     *
     * @return BaseDataProvider
     */
    public function search($q)
    {
        $searchModel = new StaffSearch();

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
        return $view->render('@modules/account/views/admin/staff/components/quick-search-result-item', compact('model'));
    }
}
