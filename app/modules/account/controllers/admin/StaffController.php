<?php namespace modules\account\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use Faker\Factory;
use modules\account\models\AccountContact;
use modules\account\models\forms\staff\StaffSearch;
use modules\account\models\forms\staff_account\StaffAccountLogin;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\core\db\ActiveRecord;
use modules\file_manager\web\UploadedFile;
use modules\ui\widgets\form\Form;
use modules\ui\widgets\lazy\Lazy;
use Pusher\Pusher;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class StaffController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        array_unshift($behaviors['access']['rules'], [
            'allow' => true,
            'actions' => ['login'],
            'roles' => ['?'],
        ]);

        return $behaviors;
    }

    /**
     * @return string
     */
    public function actionDashboard()
    {
        return $this->render('dashboard');
    }


    /**
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goBack(Yii::$app->getHomeUrl());
        }

        $model = new StaffAccountLogin();
        $data = Yii::$app->request->post();

        $model->loadDefaultValues();

        if ($model->load($data)) {
            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate($model);
            }

            if ($model->login()) {
                Yii::$app->session->addFlash('success', Yii::t('app', 'Hi {username}, welcome to Eflima Engine', [
                    'username' => $model->username,
                ]));

                return $this->redirect(['index']);
            } elseif ($model->hasErrors()) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to login for unknown reason'));
            }
        }

        $this->layout = 'admin/unauthenticated';

        return $this->render('login', compact('model'));
    }

    /**
     * @return Response
     */
    public function actionLogout()
    {
        if (Yii::$app->user->logout()) {
            return $this->redirect(Yii::$app->user->loginUrl);
        }

        Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to {action}', [
            'action' => Yii::t('app', 'log out'),
        ]));

        return $this->goBack(Yii::$app->getHomeUrl());
    }

    /**
     * @return array|string|Response
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;

        $searchModel = new StaffSearch();

        if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $searchModel->load($params);

            return Form::validate($searchModel);
        }

        $searchModel->apply($params);

        return $this->render('index', compact('searchModel'));
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     *
     * @throws InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->getModel($id, Staff::class);

        if (!($model instanceof Staff)) {
            return $model;
        }

        $model->scenario = 'admin/update';

        $model->accountModel = $model->account;
        $model->accountModel->scenario = 'admin/update';

        $model->accountModel->contactModel = $model->account->contact;
        $model->accountModel->contactModel->scenario = 'admin/update';

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param integer             $id
     * @param string|ActiveRecord $modelClass
     * @param null|Closure        $queryFilter
     *
     * @return string|Response|Staff
     *
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = Staff::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        return $model;
    }

    /**
     * @param Staff         $model
     * @param               $data
     *
     * @return string|array
     */
    protected function modify($model, $data)
    {
        $model->loadDefaultValues();
        $model->accountModel->loadDefaultValues();
        $model->accountModel->contactModel->loadDefaultValues();

        if (
            $model->load($data) &&
            $model->accountModel->load($data) &&
            $model->accountModel->contactModel->load($data)
        ) {
            $model->accountModel->uploaded_avatar = UploadedFile::getInstance($model->accountModel, 'uploaded_avatar');

            if (Yii::$app->request->getHeaders()->get('X-Validate') == 1) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return Form::validate(
                    $model,
                    $model->accountModel,
                    $model->accountModel->contactModel
                );
            }

            if ($model->save()) {
                Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully saved', [
                    'object' => Yii::t('app', 'Staff'),
                    'object_name' => $model->name,
                ]));

                if (Lazy::isLazyModalRequest() || Lazy::isLazyInsideModalRequest()) {
                    Lazy::close();

                    return;
                }

                return $this->redirect(['update', 'id' => $model->id]);
            } elseif (
                $model->hasErrors() ||
                $model->accountModel->hasErrors() ||
                $model->accountModel->contactModel->hasErrors()
            ) {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Some of the information you entered is invalid'));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to save {object}', [
                    'object' => Yii::t('app', 'Staff'),
                ]));
            }
        }

        return $this->render('modify', compact('model'));
    }

    /**
     * @return array|string|Response
     */
    public function actionAdd()
    {
        $model = new Staff([
            'scenario' => 'admin/add',
        ]);
        $model->accountModel = new StaffAccount([
            'scenario' => 'admin/add',
        ]);
        $model->accountModel->contactModel = new AccountContact([
            'scenario' => 'admin/add',
        ]);

        return $this->modify($model, Yii::$app->request->post());
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Staff)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully deleted', [
                'object' => Yii::t('app', 'Staff'),
                'object_name' => $model->name,
            ]));

            if (Lazy::isLazyModalRequest()) {
                Lazy::close();

                return '';
            }
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to delete {object}', [
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        return $this->redirect(['index']);
    }

    /**
     * @param int|string $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionView($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Staff)) {
            return $model;
        }

        return $this->render('view', compact('model'));
    }

    /**
     * @param int|string         $id
     * @param int|string|boolean $block
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionBlock($id, $block = 1)
    {
        $model = $this->getModel($id);

        if (!($model instanceof Staff)) {
            return $model;
        }

        if ($model->account->block(intval($block))) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object_name} successfully blocked', [
                'object' => Yii::t('app', 'Staff'),
                'object_name' => $model->name,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to block {object}', [
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        return $this->redirect(['index']);
    }

    /**
     * @return array
     *
     * @throws InvalidConfigException
     * @throws MethodNotAllowedHttpException
     */
    public function actionAutoComplete()
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('This URL only serve ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new StaffSearch();

        return $searchModel->autoComplete(Yii::$app->request->queryParams);
    }

    public function actionGenerate($number = 30)
    {
        $faker = Factory::create();

        $transaction = Yii::$app->db->beginTransaction();

        while ($number > 0) {
            $model = new Staff([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'scenario' => 'admin/add',
            ]);
            $model->accountModel = new StaffAccount([
                'password' => 'rifan1234',
                'password_repeat' => 'rifan1234',
                'username' => $faker->userName,
                'email' => $faker->email,
                'scenario' => 'admin/add',
            ]);
            $model->accountModel->contactModel = new AccountContact([
                'phone' => $faker->phoneNumber,
                'whatsapp' => $faker->phoneNumber,
                'address' => $faker->address,
                'scenario' => 'admin/add',
            ]);

            if (!$model->save()) {
                $transaction->rollBack();

                return false;
            }

            $number--;
        }

        $transaction->commit();

        return true;
    }

    public function actionTest()
    {
        $options = [
            'cluster' => 'ap1',
            'useTLS' => false,
        ];
        $pusher = new Pusher(
            'feb6d51f436b99f2fba4',
            'b50f5b9b72ada7da37fe',
            '803272',
            $options
        );

        $data['message'] = 'hello world';
        $a = $pusher->trigger('my-channel', 'my-event', $data);

        echo "<pre>";
        var_dump($a);
        echo "</pre>";
        exit;
    }
}
