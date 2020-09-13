<?php namespace modules\account\widgets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\assets\admin\StaffCommentAsset;
use modules\account\models\AccountComment;
use modules\account\models\forms\account_comment\AccountCommentSearch;
use modules\ui\widgets\form\Form;
use yii\base\Widget;
use yii\data\ActiveDataProvider;
use yii\data\BaseDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property AccountCommentSearch $searchModel
 * @property AccountComment       $newModel
 * @property ActiveDataProvider   $dataProvider
 */
class StaffCommentWidget extends Widget
{
    /** @var array|Form */
    public $form = [];

    public $action = ['/account/admin/staff-comment/add'];

    public $jsOptions = [];
    public $options = [];

    public $relatedModel;
    public $relatedModelId;

    /** @var AccountCommentSearch */
    protected $_searchModel;

    /** @var ActiveDataProvider */
    protected $_dataProvider;

    protected $params;

    public function init()
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
            $this->options['data-rid'] = $this->getRealId();
        }

        parent::init();
    }

    /**
     * @return AccountCommentSearch
     */
    public function getSearchModel()
    {
        if (isset($this->_searchModel)) {
            return $this->_searchModel;
        }

        $searchModel = new AccountCommentSearch();
        $query = $searchModel->getQuery();

        if (isset($this->relatedModel)) {
            $searchModel->params['model'] = $this->relatedModel;

            $query->andWhere(['model' => $this->relatedModel]);
        }

        if (isset($this->relatedModelId)) {
            $searchModel->params['model_id'] = $this->relatedModelId;

            $query->andWhere(['model_id' => $this->relatedModelId]);
        }

        return $searchModel;
    }

    /**
     * @return ActiveDataProvider|BaseDataProvider
     */
    public function getDataProvider()
    {
        if (isset($this->_dataProvider)) {
            return $this->_dataProvider;
        }

        $this->searchModel->apply($this->params);

        return $this->searchModel->dataProvider;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->normalize();

        $form = $this->renderForm();
        $items = $this->renderItems();

        $this->registerAssets();

        return $form . $items;
    }

    public function registerAssets()
    {
        StaffCommentAsset::register($this->view);

        $this->jsOptions['form'] = '#' . $this->form->id;
        $jsOptions = Json::encode($this->jsOptions);

        $this->view->registerJs("$('#{$this->options['id']}').staffComment({$jsOptions})");
    }

    public function normalize()
    {
        $this->form = ArrayHelper::merge([
            'id' => 'account-comment-form',
            'action' => $this->action,
            'autoRenderActions' => false,
            'enableTimestamp' => false,
            'formActionsSections' => [
                'secondary' => [
                    'sort' => -1,
                    'class' => 'align-self-start d-flex ',
                ],
            ],
        ], $this->form);
    }

    /**
     * @return AccountComment
     */
    public function getNewModel()
    {
        return new AccountComment([
            'model' => $this->relatedModel,
            'model_id' => $this->relatedModelId,
        ]);
    }

    /**
     * @return string
     */
    public function renderForm()
    {
        $model = $this->getNewModel();

        return $this->render('@modules/account/views/admin/staff-comment/components/form', [
            'widget' => $this,
            'model' => $model,
        ]);
    }

    /**
     * @return string
     */
    public function renderItems()
    {
        $dataProvider = $this->getDataProvider();

        /** @var AccountComment[] $models */
        $models = $dataProvider->models;
        $result = '';

        foreach ($models AS $model) {
            $result .= $this->render('@modules/account/views/admin/staff-comment/components/data-list-item', compact('model'));
        }

        return Html::tag('div', $result, $this->options);
    }
}