<?php namespace modules\content\controllers\rest\v1;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\rest\Controller;
use modules\content\models\Post;
use modules\content\models\forms\post\PostSearch;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\data\BaseDataProvider;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class PostController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['optional'] = [
            'get',
            'list',
        ];

        return $behaviors;
    }

    /**
     * @inheritDoc
     */
    public function verbs()
    {
        return [
            'get' => ['get'],
            'list' => ['get'],
        ];
    }

    /**
     * @param null $id
     *
     * @return null|Post|ActiveDataProvider|BaseDataProvider|array
     * @throws InvalidConfigException
     */
    public function actionGet($id = null)
    {
        if (empty($id)) {
            return $this->actionList();
        }

        $model = $this->getModel($id);

        if (!$model) {
            $this->failed()->addMessage('danger', Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Post'),
            ]));

            return null;
        }

        return $model;
    }

    /**
     * @return ActiveDataProvider|BaseDataProvider
     */
    public function actionList()
    {
        $searchModel = new PostSearch();

        return $searchModel->apply(Yii::$app->request->queryParams, '');
    }

    /**
     * @param string|int $id
     *
     * @return array|Post|null
     * @throws InvalidConfigException
     */
    public function getModel($id)
    {
        return Post::find()->andWhere(['id' => $id])->one();
    }
}