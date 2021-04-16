<?php namespace modules\calendar\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\models\Staff;
use modules\account\web\admin\Controller;
use modules\calendar\models\Event;
use modules\calendar\models\EventMember;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\Response;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class EventMemberController extends Controller
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
                'actions' => ['invite'],
                'verbs' => ['POST'],
                'roles' => ['admin.event.update', 'admin.event.add'],
            ],
            [
                'allow' => true,
                'actions' => ['delete'],
                'verbs' => ['POST', 'DELETE'],
                'roles' => ['admin.event.update', 'admin.event.add'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @param string             $id
     * @param string|EventMember $modelClass
     * @param null|Closure       $queryFilter
     *
     * @return string|Response|EventMember
     * @throws InvalidConfigException
     */
    public function getModel($id, $modelClass = EventMember::class, $queryFilter = null)
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

        if (!($model instanceof EventMember)) {
            return $model;
        }

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{object} ({object_name}) successfully removed from {target_object}', [
                'object' => Yii::t('app', 'Staff'),
                'target_object' => Yii::t('app', 'Event'),
                'object_name' => $model->staff->name,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to remove {object} from {target_object}', [
                'target_object' => Yii::t('app', 'Event'),
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        return $this->redirect(['/calendar/admin/event/view', 'id' => $model->event_id]);
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
        $model = Event::find()->andWhere(['id' => $id])->one();

        if (!($model instanceof Event)) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Event'),
            ]));
        }

        $staff = Staff::find()->andWhere(['id' => $staff_id])->one();

        if (!$staff) {
            return $this->notFound(Yii::t('app', '{object} you are looking for doesn\'t exists', [
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        if ($model->invite($staff_id)) {
            Yii::$app->session->addFlash('success', Yii::t('app', '{staff} invited to this event', [
                'staff' => $staff->name,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to invite to event'));
        }

        return $this->redirect(['/calendar/admin/event/view', 'id' => $model->id]);
    }
}
