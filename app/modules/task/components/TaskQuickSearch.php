<?php namespace modules\task\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\forms\staff\StaffSearch;
use modules\account\web\admin\View;
use modules\quick_access\components\QuickSearch;
use modules\task\models\forms\task\TaskSearch;
use Yii;
use yii\data\BaseDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class TaskQuickSearch extends QuickSearch
{

    /**
     * @return string
     */
    public function getLabel()
    {
        return Yii::t('app','Task');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'task';
    }

    /**
     * @inheritDoc
     */
    public function isActive()
    {
        return Yii::$app->user->can('admin.task.list');
    }

    /**
     * @param $q
     *
     * @return BaseDataProvider
     */
    public function search($q)
    {
        $searchModel = new TaskSearch();

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
        return $view->render('@modules/task/views/admin/task/components/quick-search-result-item', compact('model'));
    }
}
