<?php namespace modules\calendar\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\account\web\admin\Controller;
use modules\calendar\models\EventMember;
use modules\ui\widgets\lazy\Lazy;
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
                'target_object' => Yii::t('app','Event'),
                'object_name' => $model->staff->name,
            ]));
        } else {
            Yii::$app->session->addFlash('danger', Yii::t('app', 'Failed to remove {object} from {target_object}', [
                'target_object' => Yii::t('app', 'Event'),
                'object' => Yii::t('app', 'Staff'),
            ]));
        }

        return $this->goBack(['index']);
    }
}