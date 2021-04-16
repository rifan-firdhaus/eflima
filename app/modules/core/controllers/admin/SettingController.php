<?php namespace modules\core\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\web\admin\Controller;
use modules\core\components\SettingRenderer;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class SettingController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['menu'],
                'verbs' => ['GET'],
                'roles' => ['@'],
            ],
            [
                'allow' => true,
                'actions' => ['index'],
                'verbs' => ['GET', 'POST'],
                'roles' => ['admin.setting.general'],
                'matchCallback' => function () {
                    return Yii::$app->request->get('section') === 'general' || Yii::$app->request->get('section') === 'crm';
                },
            ],
        ];

        return $behaviors;
    }

    /**
     * @return string
     */
    public function actionMenu()
    {
        return $this->renderPartial('menu');
    }

    /**
     * @param $section
     *
     * @return array|bool|string|Response
     * @throws Throwable
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionIndex($section)
    {
        /** @var SettingRenderer $renderer */
        $renderer = Yii::createObject([
            'class' => SettingRenderer::class,
            'section' => $section,
            'view' => $this->view,
        ]);

        $result = $renderer->loadAndSave($this, Yii::$app->request->post());

        if ($result !== false) {
            return $result;
        }

        return $this->render('index', compact('renderer'));
    }
}
