<?php namespace modules\core\controllers\admin;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use Faker\Factory;
use Faker\Generator;
use modules\account\models\AccountComment;
use modules\account\models\AccountContact;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use modules\account\web\admin\Controller;
use modules\address\models\Country;
use modules\calendar\models\Event;
use modules\crm\models\Customer;
use modules\crm\models\CustomerContact;
use modules\crm\models\CustomerContactAccount;
use modules\crm\models\CustomerGroup;
use modules\finance\components\Payment;
use modules\finance\models\Currency;
use modules\finance\models\Expense;
use modules\finance\models\ExpenseCategory;
use modules\finance\models\Invoice;
use modules\finance\models\InvoiceItem;
use modules\finance\models\InvoicePayment;
use modules\finance\models\Product;
use modules\project\models\Project;
use modules\project\models\ProjectMilestone;
use modules\project\models\ProjectStatus;
use modules\task\models\Task;
use modules\task\models\TaskChecklist;
use modules\task\models\TaskInteraction;
use modules\task\models\TaskPriority;
use modules\task\models\TaskStatus;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\db\Exception as DbException;
use yii\helpers\Html;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Generator $faker
 * @property mixed     $currency
 */
class GeneratorController extends Controller
{
    protected $_faker;
    protected $errors = [];

    public function actionGenerate($factor = 10)
    {
        $methods = ['customer', 'project', 'staff', 'event', 'expense', 'task', 'product', 'invoice'];

        $transaction = Yii::$app->db->beginTransaction();

        try {
            while ($factor > 0) {
                $methodIndex = array_rand($methods);
                $method = $methods[$methodIndex];

                if (!call_user_func([$this, $method])) {
                    $transaction->rollBack();

                    echo "<pre>";
                    var_dump($method);
                    print_r($this->errors);
                    echo "</pre>";
                    exit;

                    return 0;
                }

                $factor--;
            }
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        $transaction->commit();

        return 1;
    }

    public function createParagraphs($num = 3, $word = 5)
    {
        $result = '';

        for ($i = 0; $i < $num; $i++) {
            if (is_array($word)) {
                $word = call_user_func_array('rand', $word);
            }

            $result .= Html::tag('p', $this->faker->paragraph($word));
        }

        return $result;
    }

    public function convertToInputDate($date)
    {
        return Yii::$app->formatter->asDatetime($date, Yii::$app->setting->get('date_input_format'));
    }

    public function convertToInputDatetime($date)
    {
        $setting = Yii::$app->setting;

        return Yii::$app->formatter->asDatetime($date, $setting->get('date_input_format') . ' ' . substr($setting->get('time_input_format'), 4));
    }

    /**
     * @return Generator
     */
    public function getFaker()
    {
        if (!isset($this->_faker)) {
            $this->_faker = Factory::create();
        }

        return $this->_faker;
    }

    public function getCurrency()
    {
        if (rand(0, 3) >= 1) {
            return Yii::$app->setting->get('finance/base_currency');
        }

        return Currency::find()->orderBy('RAND()')->select('code')->createCommand()->queryScalar();
    }

    public function getCountry()
    {
        return Country::find()->orderBy('RAND()')->select('code')->createCommand()->queryScalar();
    }

    public function staff()
    {
        $faker = $this->faker;
        $time = time();
        $createdAt = $time - rand(0, 604800); // -1 weeks from $time

        $model = new Staff([
            'scenario' => 'admin/add',
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        $model->accountModel = new StaffAccount([
            'scenario' => 'admin/add',
            'username' => $faker->userName,
            'password' => 'rifan123',
            'password_repeat' => 'rifan123',
            'email' => $faker->email,
        ]);
        $model->accountModel->contactModel = new AccountContact([
            'scenario' => 'admin/add',
            'phone' => $faker->phoneNumber,
            'address' => $faker->address,
        ]);

        $attributeBehavior = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => [],
            BaseActiveRecord::EVENT_BEFORE_UPDATE => [],
        ];

        // Reset Timestamp Behavior
        $model->getBehavior('timestamp')->attributes = $attributeBehavior;
        $model->accountModel->getBehavior('timestamp')->attributes = $attributeBehavior;

        $model->loadDefaultValues();
        $model->accountModel->loadDefaultValues();
        $model->accountModel->contactModel->loadDefaultValues();

        if (!$model->save()) {
            $this->errors['staff'][] = $model->errors;

            return false;
        }

        return true;
    }

    public function getStaff($field = 'id')
    {
        $id = Staff::find()->orderBy('RAND()')->select($field)->createCommand()->queryScalar();

        if (!$id) {
            if (!$this->staff()) {
                return false;
            }

            return $this->getStaff();
        }

        return $id;
    }

    public function getCustomerGroup($new = false)
    {
        if (rand(0, 5) === 1 || $new) {
            $model = new CustomerGroup([
                'name' => $this->faker->word,
                'description' => $this->faker->text,
            ]);

            $model->loadDefaultValues();

            if (!$model->save()) {
                return false;
            }


            return $model->id;
        }

        $id = CustomerGroup::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar();

        if (!$id) {
            return $this->getCustomerGroup(true);
        }

        return $id;
    }

    public function getExpenseCategory()
    {
        $id = ExpenseCategory::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar();

        if (!$id) {
            if (!$this->expenseCategory()) {
                return false;
            }

            return $this->getExpenseCategory();
        }

        return $id;
    }

    public function expenseCategory()
    {
        $model = new ExpenseCategory([
            'name' => $this->faker->word,
            'description' => $this->faker->text(rand(20, 30)),
        ]);

        $model->loadDefaultValues();

        if (!$model->save()) {
            return false;
        }


        return $model->id;
    }

    public function getProjectStatus()
    {
        return ProjectStatus::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar();
    }

    public function expense($customerId = null, $projectId = null, $time = null)
    {
        $faker = $this->faker;

        if ($time) {
            $createdAt = $time + rand(0, 604800); // -1 weeks from $time
        } else {
            $time = time();
            $createdAt = $time - rand(0, 604800); // -1 weeks from $time
        }

        $date = $time - (rand(0, 604800) * ($faker->boolean ? -1 : 1)); // -1 or +1 weeks from $time
        $categoryId = $this->getExpenseCategory();

        if (!$categoryId) {
            return false;
        }

        if (!$customerId && $faker->boolean) {
            $customerId = $this->getCustomer();

            if (!$customerId) {
                return false;
            }
        }

        if (!$projectId && $faker->boolean) {
            $projectId = $this->getProject();

            if (!$projectId) {
                return false;
            }
        }

        $model = new Expense([
            'name' => $faker->text(rand(20, 40)),
            'date' => $date,
            'customer_id' => $customerId,
            'project_id' => $projectId,
            'reference' => rand(100000, 999999),
            'description' => $faker->text(rand(5, 20)),
            'currency_rate' => 1,
            'amount' => rand(10, 90000),
            'is_billable' => $faker->boolean,
            'category_id' => $categoryId,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $attributeBehavior = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => [],
            BaseActiveRecord::EVENT_BEFORE_UPDATE => [],
        ];

        // Reset Timestamp Behavior
        $model->getBehavior('timestamp')->attributes = $attributeBehavior;

        $model->loadDefaultValues();

        if (!$model->save()) {
            return false;
        }

        if (rand(0, 5) === 0) {
            for ($i = 1; $i <= rand(1, 4); $i++) {
                if (!$this->task('expense', $model->id, $model->created_at)) {
                    return false;
                }
            }
        }

        for ($i = 1; $i <= rand(1, 7); $i++) {
            if (!$this->comment('expense', $model->id, $model->created_at)) {
                return false;
            }
        }

        unset($model);

        return true;
    }

    public function project()
    {
        $faker = $this->faker;
        $time = time();
        $createdAt = $time - rand(0, 604800); // -1 weeks from $time
        $startDate = $createdAt - rand(-604800, 604800); // -1 weeks or +1 week from $createdAt
        $deadlineDate = $startDate + rand(0, 5184000); // +1 months from $startDate
        $visiblities = Project::visibilities();
        $visiblity = array_rand($visiblities);
        $customerId = $this->getCustomer();

        if (!$customerId) {
            return false;
        }

        $model = new Project([
            'scenario' => 'admin/add',
            'customer_id' => $customerId,
            'name' => $faker->text(rand(20, 40)),
            'started_date' => $this->convertToInputDatetime($startDate),
            'deadline_date' => $this->convertToInputDatetime($deadlineDate),
            'status_id' => $this->getProjectStatus(),
            'description' => $this->createParagraphs(rand(1, 50)),
            'visibility' => $visiblity,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $attributeBehavior = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => [],
            BaseActiveRecord::EVENT_BEFORE_UPDATE => [],
        ];

        // Reset Timestamp Behavior
        $model->getBehavior('timestamp')->attributes = $attributeBehavior;

        $model->loadDefaultValues();

        if (!$model->save()) {
            $this->errors['project'][] = $model->errors;

            return false;
        }

        for ($i = 1; $i <= rand(2, 7); $i++) {
            if (!$this->projectMilestone($model)) {
                return false;
            }
        }

        if (rand(0, 3) === 0) {
            for ($i = 1; $i <= rand(1, 3); $i++) {
                if (!$this->event('project', $model->id, $model->created_at)) {
                    return false;
                }
            }
        }

        for ($i = 1; $i <= rand(1, 7); $i++) {
            $milestone = rand(0, 4) >= 1 ? $this->getProjectMilestone($model) : null;

            if (!$this->task('project', $model->id, $model->created_at, $milestone)) {
                return false;
            }
        }

        if ($faker->boolean) {
            for ($i = 1; $i <= rand(1, 3); $i++) {
                if (!$this->invoice($model->customer_id, $model->id, $model->created_at)) {
                    return false;
                }
            }
        }

        if ($faker->boolean) {
            for ($i = 1; $i <= rand(1, 3); $i++) {
                if (!$this->expense($model->customer_id, $model->id, $model->created_at)) {
                    return false;
                }
            }
        }

        unset($model);

        return true;
    }

    public function getProject()
    {
        $id = Project::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar();

        if (!$id) {
            if (!$this->project()) {
                return false;
            }

            return $this->getProject();
        }

        return $id;
    }


    /**
     * @param $project
     *
     * @return bool|false|string|null
     * @throws InvalidConfigException
     * @throws DbException
     */
    public function getProjectMilestone($project)
    {
        $id = ProjectMilestone::find()->orderBy('RAND()')->andWhere(['project_id' => $project->id])->select('id')->createCommand()->queryScalar();

        if (!$id) {
            if (!$this->projectMilestone($project)) {
                return false;
            }

            return $this->getProjectMilestone($project);
        }

        return $id;
    }

    /**
     * @param Project $project
     *
     * @return bool
     */
    public function projectMilestone($project)
    {
        $faker = $this->faker;
        $time = $project->created_at;
        $createdAt = $time + rand(0, 604800); // -1 weeks from $time
        $startDate = $createdAt - rand(-604800, 604800); // -1 weeks or +1 week from $createdAt
        $deadlineDate = $startDate + rand(0, 5184000); // +1 months from $startDate
        $colors = ProjectMilestone::colors();
        $color = array_rand($colors);

        $model = new ProjectMilestone([
            'scenario' => 'admin/add',
            'name' => $faker->text(rand(20, 45)),
            'project_id' => $project->id,
            'started_date' => $this->convertToInputDatetime($startDate),
            'deadline_date' => $this->convertToInputDatetime($deadlineDate),
            'color' => $color,
            'description' => $faker->paragraph(rand(1, 2)),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $attributeBehavior = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => [],
            BaseActiveRecord::EVENT_BEFORE_UPDATE => [],
        ];

        // Reset Timestamp Behavior
        $model->getBehavior('timestamp')->attributes = $attributeBehavior;

        $model->loadDefaultValues();

        if (!$model->save()) {
            $this->errors['project_milestone'][] = $model->errors;

            return false;
        }

        unset($model);

        return true;
    }

    public function event($relatedModel = null, $relatedModelId = null, $time = null)
    {
        $faker = $this->getFaker();
        $time = $time ? $time : time();
        $createdAt = $time - rand(0, 604800); // -1 weeks from $time
        $startDate = $createdAt - rand(0, 604800); // -1 weeks or +1 week from $createdAt
        $endDate = $startDate + rand(0, 604800); // +1 week from $startDate

        if ($relatedModel === null && $faker->boolean) {
            $models = ['project', 'customer'];
            $modelIndex = array_rand($models);
            $relatedModel = $models[$modelIndex];
            $relatedModelId = call_user_func([$this, 'get' . ucfirst($relatedModel)]);
        }

        $model = new Event([
            'scenario' => 'admin/add',
            'model' => $relatedModel,
            'model_id' => $relatedModelId,
            'description' => $faker->paragraph(rand(1, 3)),
            'name' => $faker->text(rand(20, 45)),
            'start_date' => $startDate,
            'location' => $faker->address,
            'end_date' => $endDate,
            'created_at' => $createdAt,
        ]);

        $memberIds = [];

        for ($i = 1; $i <= rand(1, 7); $i++) {
            $memberIds[] = $this->getStaff();
        }

        $model->member_ids = array_unique($memberIds);

        $attributeBehavior = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => [],
            BaseActiveRecord::EVENT_BEFORE_UPDATE => [],
        ];

        // Reset Timestamp Behavior
        $model->getBehavior('timestamp')->attributes = $attributeBehavior;

        $model->loadDefaultValues();

        if (!$model->save()) {
            $this->errors['event'] = $model->errors;

            return false;
        }

        for ($i = 1; $i <= rand(1, 7); $i++) {
            if (!$this->comment('event', $model->id, $model->created_at)) {
                return false;
            }
        }

        unset($model);

        return true;
    }

    public function customer()
    {
        $faker = $this->getFaker();
        $time = time();
        $createdAt = $time - rand(0, 604800); // - 1 weeks from $time
        $customerGroup = $this->getCustomerGroup();

        if (!$customerGroup) {
            return false;
        }

        $model = new Customer([
            'scenario' => 'admin/add',
            'company_name' => $faker->company,
            'group_id' => $customerGroup,
            'currency_code' => $this->getCurrency(),
            'phone' => $faker->phoneNumber,
            'email' => $faker->email,
            'fax' => $faker->phoneNumber,
            'city' => $faker->city,
            'province' => $faker->city,
            'country_code' => $this->getCountry(),
            'address' => $faker->streetAddress,
            'postal_code' => $faker->postcode,
            'vat_number' => $faker->creditCardNumber,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $model->primaryContactModel = new CustomerContact([
            'scenario' => 'admin/add',
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'city' => $faker->city,
            'province' => $faker->city,
            'country_code' => $this->getCountry(),
            'address' => $faker->streetAddress,
            'postal_code' => $faker->postcode,
            'phone' => $faker->phoneNumber,
            'mobile' => $faker->phoneNumber,
            'email' => $faker->email,
            'has_customer_area_access' => true,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $model->primaryContactModel->accountModel = new CustomerContactAccount([
            'scenario' => 'admin/add',
            'password' => 'rifan123',
            'password_repeat' => 'rifan123',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $attributeBehavior = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => [],
            BaseActiveRecord::EVENT_BEFORE_UPDATE => [],
        ];

        // Reset Timestamp Behavior
        $model->getBehavior('timestamp')->attributes = $attributeBehavior;
        $model->primaryContactModel->getBehavior('timestamp')->attributes = $attributeBehavior;
        $model->primaryContactModel->accountModel->getBehavior('timestamp')->attributes = $attributeBehavior;

        $model->loadDefaultValues();
        $model->primaryContactModel->loadDefaultValues();
        $model->primaryContactModel->accountModel->loadDefaultValues();

        if (!$model->save()) {
            return false;
        }

        if (rand(0, 3) === 0) {
            for ($i = 1; $i <= rand(1, 4); $i++) {
                if (!$this->event('customer', $model->id, $model->created_at)) {
                    return false;
                }
            }
        }

        if (rand(0, 1) === 0) {
            for ($i = 1; $i <= rand(1, 5); $i++) {
                if (!$this->task('customer', $model->id, $model->created_at)) {
                    return false;
                }
            }
        }

        if ($faker->boolean) {
            for ($i = 1; $i <= rand(1, 3); $i++) {
                if (!$this->invoice($model->id, null, $model->created_at)) {
                    return false;
                }
            }
        }

        if ($faker->boolean) {
            for ($i = 1; $i <= rand(1, 3); $i++) {
                if (!$this->expense($model->id, null, $model->created_at)) {
                    return false;
                }
            }
        }

        unset($model);

        return true;
    }

    public function getCustomer()
    {
        $id = Customer::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar();

        if (!$id) {
            if (!$this->customer()) {
                return false;
            }

            return $this->getCustomer();
        }

        return $id;
    }

    public function getTaskStatus()
    {
        return TaskStatus::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar();
    }

    public function getTaskPriority()
    {
        return TaskPriority::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar();
    }

    public function task($relatedModel = null, $relatedModelId = null, $time = null, $milestone = null)
    {
        $faker = $this->getFaker();
        $time = $time ? $time : time();
        $createdAt = $time - rand(0, 604800 * 4); // -1 weeks from $time
        $startDate = $createdAt - (rand(0, 604800 * 2) * ($faker->boolean ? -1 : 1)); // -2 weeks or +2 week from $createdAt
        $deadlineDate = $startDate + rand(0, 604800 * 8); // +2 week from $startDate
        $visibilities = Task::visibilities();
        $visibility = array_rand($visibilities);
        $progressCalculations = Task::progressCalculations();

        unset($progressCalculations[Task::PROGRESS_CALCULATION_SUBTASK]);

        $progressCalculation = array_rand($progressCalculations);
        $creatorId = $this->getStaff();

        if (!$creatorId) {
            return false;
        }

        if ($relatedModel === null && $faker->boolean) {
            $models = ['project', 'customer'];
            $modelIndex = array_rand($models);
            $relatedModel = $models[$modelIndex];
            $relatedModelId = call_user_func([$this, 'get' . ucfirst($relatedModel)]);
        }

        $model = new Task([
            'scenario' => 'admin/add',
            'creator_id' => $creatorId,
            'title' => $faker->text(rand(20, 45)),
            'description' => $this->createParagraphs(rand(1, 5), [2, 20]),
            'is_billable' => $faker->boolean,
            'is_timer_enabled' => $faker->boolean,
            'priority_id' => $this->getTaskPriority(),
            'status_id' => $this->getTaskStatus(),
            'progress_calculation' => $progressCalculation,
            'model' => $relatedModel,
            'model_id' => $relatedModelId,
            'started_date' => $startDate,
            'deadline_date' => $deadlineDate,
            'visibility' => $visibility,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $model->milestone_id = $milestone;

        $assigneeIds = [];
        $checklists = [];

        for ($i = 0; $i <= rand(1, 20); $i++) {
            $assigneeIds[] = $this->getStaff();
        }

        for ($i = 0; $i <= rand(1, 20); $i++) {
            $checklists['__' . rand(10000, 99999)] = [
                'label' => $faker->text(rand(20, 45)),
                'is_checked' => $faker->boolean,
            ];
        }

        $model->checklists = $checklists;
        $model->assignee_ids = array_unique($assigneeIds);

        $attributeBehavior = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => [],
            BaseActiveRecord::EVENT_BEFORE_UPDATE => [],
        ];

        // Reset Timestamp Behavior
        $model->getBehavior('timestamp')->attributes = $attributeBehavior;

        $model->loadDefaultValues();

        if (!$model->save()) {
            $this->errors['task'] = $model->errors;

            return false;
        }

        /** @var TaskChecklist[] $checklists */
        $checklists = $model->getChecklists()->all();

        foreach ($checklists AS $checklist) {
            // Reset Timestamp Behavior
            $checklist->getBehavior('timestamp')->attributes = $attributeBehavior;

            $checklist->created_at = $createdAt;
            $checklist->updated_at = $createdAt;

            if ($checklist->is_checked) {
                $checklist->checked_at = $createdAt;
            }

            if (!$checklist->save()) {
                $this->errors['task_checklist'][] = $model->errors;

                return false;
            }
        }

        $interaction = TaskInteraction::find()->andWhere(['task_id' => $model->id])->one();

        $interaction->at = $createdAt;
        $interaction->staff_id = $model->creator_id;

        if (!$interaction->save(false)) {
            return false;
        }

        for ($i = 0; $i <= rand(0, 35); $i++) {
            if (!$this->taskInteraction($model)) {
                return false;
            }
        }

        unset($model);

        return true;
    }

    /**
     * @param Task $task
     *
     * @return bool
     */
    public function taskInteraction($task)
    {
        $time = time();
        $createdAt = $time - (rand(0, $time - $task->created_at));
        $staffId = $this->getStaff();

        if (!$staffId) {
            return false;
        }

        $model = new TaskInteraction([
            'scenario' => 'admin/add',
            'task_id' => $task->id,
            'staff_id' => $staffId,
            'at' => $createdAt,
        ]);

        if (rand(0, 5) === 0) {
            $statusId = $this->getTaskStatus();

            if (!$statusId) {
                return false;
            }

            $model->status_id = $statusId;
        }

        if (rand(0, 3) >= 1 && $task->progress_calculation === Task::PROGRESS_CALCULATION_OWN) {
            $model->progress = rand(0, 100);
        }

        $model->comment = $this->createParagraphs(rand(1, 2), [1, 3]);

        if (!$model->save()) {
            $this->errors['task_interaction'][] = $model->errors;

            return false;
        }

        unset($model);

        return true;
    }

    public function comment($relatedModel = null, $relatedModelId = null, $time = null)
    {
        $faker = $this->getFaker();
        $time = $time ? $time : time();
        $createdAt = $time + rand(0, 604800 * 4); // -1 weeks from $time
        $staffId = $this->getStaff('account_id');

        if ($relatedModel === null && $faker->boolean) {
            $models = ['project', 'customer'];
            $modelIndex = array_rand($models);
            $relatedModel = $models[$modelIndex];
            $relatedModelId = call_user_func([$this, 'get' . ucfirst($relatedModel)]);
        }

        $model = new AccountComment([
            'scenario' => 'admin/add',
            'account_id' => $staffId,
            'comment' => $this->createParagraphs(rand(1, 2), [4, 15]),
            'model' => $relatedModel,
            'model_id' => $relatedModelId,
            'posted_at' => $createdAt,
        ]);

        $model->loadDefaultValues();

        $attributeBehavior = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => [],
            BaseActiveRecord::EVENT_BEFORE_UPDATE => [],
        ];

        // Reset Timestamp Behavior
        $model->getBehavior('timestamp')->attributes = $attributeBehavior;

        if (!$model->save(false)) {
            $this->errors['comment'][] = $model->errors;

            return false;
        }

        unset($model);

        return true;
    }

    public function product()
    {
        $faker = $this->getFaker();
        $time = time();
        $createdAt = $time - rand(0, 604800 * 4); // -4 weeks from $time

        $model = new Product([
            'scenario' => 'admin/add',
            'name' => $faker->text(rand(10, 25)),
            'description' => $faker->text(rand(10, 25)),
            'price' => rand(0, 3) >= 1 ? rand(5, 300) : rand(5, 9999),
            'is_enabled' => 1,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $model->loadDefaultValues();

        $attributeBehavior = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => [],
            BaseActiveRecord::EVENT_BEFORE_UPDATE => [],
        ];

        // Reset Timestamp Behavior
        $model->getBehavior('timestamp')->attributes = $attributeBehavior;

        if (!$model->save()) {
            $this->errors['product'][] = $model->errors;

            return false;
        }

        unset($model);

        return true;
    }

    public function getProduct($notProduct = [])
    {
        $query = Product::find()->orderBy('RAND()');

        if ($notProduct) {
            $query->andWhere(['id' => $notProduct]);
        }

        $id = $query->createCommand()->queryOne();

        if (!$id) {
            if (!$this->product()) {
                return false;
            }

            return $this->getProduct();
        }

        return $id;
    }

    public function invoice($customerId = null, $projectId = null, $time = null)
    {
        $faker = $this->getFaker();

        if ($time) {
            $createdAt = $time + rand(0, 604800 * 4); // +4 weeks from $time
        } else {
            $time = time();
            $createdAt = $time - rand(0, 604800 * 4); // -4 weeks from $time
        }

        $startDate = $createdAt - (rand(0, 604800 * 2) * ($faker->boolean ? -1 : 1)); // -2 weeks or +2 week from $createdAt
        $deadlineDate = $startDate + rand(0, 604800 * 4); // +2 week from $startDate

        if (!$customerId) {
            $customerId = $this->getCustomer();
        }

        if (!$customerId) {
            return false;
        }

        if (!$projectId && rand(0, 1) === 1) {
            $projectId = $this->getProject();
        }

        if ($projectId === false) {
            return false;
        }

        $model = new Invoice([
            'scenario' => 'admin/add',
            'number' => 'INV-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
            'date' => $startDate,
            'due_date' => $deadlineDate,
            'customer_id' => $customerId,
            'project_id' => $projectId,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $assigneeIds = [];

        for ($i = 0; $i <= rand(1, 20); $i++) {
            $assigneeIds[] = $this->getStaff();
        }

        $model->assignee_ids = array_unique($assigneeIds);

        $attributeBehavior = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => [],
            BaseActiveRecord::EVENT_BEFORE_UPDATE => [],
        ];

        $existsProduct = [];

        for ($i = 0; $i <= rand(1, 7); $i++) {
            if (rand(0, 1) === 1) {
                $product = $this->getProduct($existsProduct);

                $existsProduct[] = $product['id'];

                $itemModel = new InvoiceItem([
                    'product_id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'amount' => rand(0, 3) >= 1 ? rand(1, 4) : rand(1, 15),
                    'type' => 'product',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            } else {
                $itemModel = new InvoiceItem([
                    'name' => $faker->text(rand(10, 20)),
                    'price' => rand(0, 3) >= 1 ? rand(5, 300) : rand(5, 9999),
                    'amount' => rand(0, 3) >= 1 ? rand(1, 4) : rand(1, 15),
                    'type' => 'raw',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }


            $itemModel->loadDefaultValues();

            // Reset Timestamp Behavior
            $itemModel->getBehavior('timestamp')->attributes = $attributeBehavior;

            $model->itemModels[] = $itemModel;
        }

        $model->loadDefaultValues();

        // Reset Timestamp Behavior
        $model->getBehavior('timestamp')->attributes = $attributeBehavior;


        if (!$model->save()) {
            $this->errors['invoice'][] = $model->errors;

            return false;
        }

        $model->refresh();

        for ($i = 0; $i <= rand(1, 7); $i++) {
            if ($model->total_due === 0) {
                continue;
            }

            if (!$this->invoicePayment($model)) {
                return false;
            }

            $model->refresh();
        }

        if (rand(0, 5) >= 4) {
            for ($i = 1; $i <= rand(1, 4); $i++) {
                if (!$this->task('invoice', $model->id, $model->created_at)) {
                    return false;
                }
            }
        }

        if (rand(0, 5) >= 4) {
            for ($i = 1; $i <= rand(1, 7); $i++) {
                if (!$this->comment('invoice', $model->id, $model->created_at)) {
                    return false;
                }
            }
        }

        unset($model);

        return true;
    }

    public function getInvoice()
    {
        $id = Invoice::find()->orderBy('RAND()')->select('id')->createCommand()->queryScalar();

        if (!$id) {
            if (!$this->invoice()) {
                return false;
            }

            return $this->getInvoice();
        }

        return $id;
    }

    /**
     * @param Invoice $invoice
     *
     * @return bool
     */
    public function invoicePayment($invoice = null)
    {
        if (!$invoice) {
            $invoiceId = $this->getInvoice();

            $invoice = Invoice::findOne($invoiceId);
        }

        if ($invoice->total_due === 0) {
            return true;
        }

        $faker = $this->getFaker();
        $time = $invoice->created_at;
        $createdAt = $time + rand(0, 604800 * 4); // -4 weeks from $time
        $methods = Payment::map();
        $method = array_rand($methods);

        $model = new InvoicePayment([
            'scenario' => 'admin/add',
            'invoice_id' => $invoice->id,
            'amount' => rand(0, 5) === 1 ? $invoice->total_due : rand(0, $invoice->total_due),
            'note' => $faker->text(rand(10, 20)),
            'method_id' => $method,
            'at' => $createdAt,
        ]);

        $model->loadDefaultValues();

        if (!$model->save()) {
            $this->errors['invoice_payment'][] = $model->errors;

            return false;
        }

        if (rand(0, 5) >= 4) {
            for ($i = 1; $i <= rand(1, 7); $i++) {
                if (!$this->comment('invoice_payment', $model->id, $model->at)) {
                    return false;
                }
            }
        }

        unset($model);

        return true;
    }

    public function actionTicket($customerId = null, $time = null)
    {
        $faker = $this->getFaker();
        $time = $time ? $time : time();
        $createdAt = $time - rand(0, 604800 * 4); // -4 weeks from $time
    }
}