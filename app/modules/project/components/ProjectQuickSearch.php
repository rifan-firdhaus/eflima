<?php namespace modules\project\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\View;
use modules\project\models\forms\project\ProjectSearch;
use modules\project\models\Project;
use modules\quick_access\components\QuickSearch;
use Yii;
use yii\data\BaseDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProjectQuickSearch extends QuickSearch
{
    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return Yii::t('app', 'Project');
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return 'project';
    }

    /**
     * @inheritDoc
     */
    public function isActive()
    {
        return Yii::$app->user->can('admin.project.list');
    }

    /**
     * @param $q
     *
     * @return BaseDataProvider
     */
    public function search($q)
    {
        $searchModel = new ProjectSearch();

        $searchModel->apply(compact('q'), '');

        return $searchModel->dataProvider;
    }

    /**
     * @param Project $model
     * @param View    $view
     *
     * @return string
     */
    public function render($model, $view)
    {
        return $view->render('@modules/project/views/admin/project/components/quick-search-result-item', compact('model'));
    }
}
