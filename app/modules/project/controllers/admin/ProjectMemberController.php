<?php namespace modules\project\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\Staff;
use modules\account\web\admin\Controller;
use modules\project\models\Project;
use modules\project\models\ProjectMember;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ProjectMemberController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [

                'allow' => true,
                'actions' => ['invite'],
                'verbs' => ['POST'],
                'roles' => ['admin.project.member', 'admin.project.add', 'admin.project.update'],
            ],
            [
                'allow' => true,
                'actions' => ['delete'],
                'verbs' => ['DELETE', 'POST'],
                'roles' => ['admin.project.member', 'admin.project.add', 'admin.project.update'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @param string               $id
     * @param string|ProjectMember $modelClass
     * @param null|Closure         $queryFilter
     *
     * @return string|Response|ProjectMember
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = ProjectMember::class, $queryFilter = null)
    {
        $query = $modelClass::find()->andWhere(['id' => $id]);

        if ($queryFilter instanceof Closure) {
            call_user_func($queryFilter, $query, $id, $modelClass);
        }

        $model = $query->one();

        if (!$model) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Member'),
            ]));
        }

        return $model;
    }

    /**
     * @param string $id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->getModel($id);

        if (!($model instanceof ProjectMember)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully removed from {target_object}', [
                'object' => Yii::t('app', 'Staff'),
                'target_object' => Yii::t('app', 'Project'),
                'object_name' => $model->staff->name,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to remove {object} from {target_object}', [
                'target_object' => Yii::t('app', 'Project'),
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        return $this->redirect(['/project/admin/project/view', 'id' => $model->project_id]);
    }

    /**
     * @param $id
     * @param $staff_id
     *
     * @return array|string|Response
     * @throws InvalidConfigException
     */
    public function actionInvite($id, $staff_id)
    {
        $model = Project::find()->andWhere(['id' => $id])->one();

        if (!($model instanceof Project)) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Project'),
            ]));
        }

        $staff = Staff::find()->andWhere(['id' => $staff_id])->one();

        if (!$staff) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        if ($model->invite($staff_id)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{staff} invited to this project', [
                'staff' => $staff->name,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to invite to project'));
        }

        return $this->redirect(['/project/admin/project/view', 'id' => $model->id]);
    }
}
