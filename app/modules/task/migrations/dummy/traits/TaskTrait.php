<?php namespace modules\task\migrations\dummy\traits;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Faker\Factory;
use modules\account\models\Staff;
use modules\task\models\Task;
use modules\task\models\TaskPriority;
use modules\task\models\TaskStatus;
use modules\task\models\TaskTimer;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait TaskTrait
{
    public function createTask()
    {
        $faker = Factory::create();
        $setting = Yii::$app->setting;
        $dateSetting = $setting->get('date_input_format') . ' ' . substr($setting->get('time_input_format'), 4);
        $start = strtotime(($faker->boolean ? '-' : '+') . rand(0, 10) . ' days');
        $deadline = strtotime('+' . rand(0, 30) . ' days', $start);
        $staffTotal = Staff::find()->count();

        if ($staffTotal > 8) {
            $staffTotal = 8;
        }

        $model = new Task([
            'scenario' => 'admin/add',
            'staff' => Staff::root(),
            'creator_id' => Staff::root()->account_id,
            'title' => $faker->words(rand(2, 8), true),
            'description' => $faker->paragraph,
            'status_id' => TaskStatus::find()
                ->orderBy('RAND()')
                ->select('id')
                ->createCommand()
                ->queryScalar(),
            'started_date' => Yii::$app->formatter->asDatetime($start, $dateSetting),
            'deadline_date' => Yii::$app->formatter->asDatetime($deadline, $dateSetting),
            'priority_id' => TaskPriority::find()
                ->orderBy('RAND()')
                ->select('id')
                ->createCommand()
                ->queryScalar(),
            'is_checklist_exists' => $faker->boolean,
            'is_timer_enabled' => $faker->boolean,
            'assignor_id' => Staff::root()->id,
            'assignee_ids' => Staff::find()
                ->select('id')
                ->orderBy('RAND()')
                ->limit(rand(1, $staffTotal))
                ->createCommand()
                ->queryColumn(),
            'progress_calculation' => array_rand(Task::progressCalculations()),
            'visibility' => array_rand(Task::visibilities()),
        ]);

        if($model->is_timer_enabled){
            $model->timer_type = array_rand(Task::timerTypes());
        }

        if ($model->is_checklist_exists) {
            $checklistNumber = rand(1, 12);

            for ($i = 0; $i < $checklistNumber; $i++) {
                $model->checklists["__" . rand(1000000, 9999999)] = [
                    'order' => $i,
                    'is_checked' => $faker->boolean,
                    'label' => $faker->words(rand(1, 6), true),
                ];
            }
        }

        if (!$model->save()) {
            return false;
        }

        if ($model->is_timer_enabled) {
            $timerCount = rand(0, 20);
            $timerInitiator = $model->getAssignees()
                ->orderBy('RAND()')
                ->select('id')
                ->createCommand()
                ->queryScalar();

            for ($i = 0; $i < $timerCount; $i++) {
                $timerStart = strtotime('+' . rand(1, 43200) . 'minutes', $model->started_date);
                $timerEnd = strtotime('+' . rand(10, 480).'minutes', $timerStart);

                $timer = new TaskTimer([
                    'scenario' => 'admin/add',
                    'task_id' => $model->id,
                    'starter_id' => $timerInitiator,
                    'stopper_id' => $timerInitiator,
                    'started_at' => Yii::$app->formatter->asDate($timerStart,$dateSetting),
                    'stopped_at' => Yii::$app->formatter->asDate($timerEnd,$dateSetting),
                ]);

                if(!$timer->save()){
                    return false;
                }
            }
        }

        return true;
    }
}
