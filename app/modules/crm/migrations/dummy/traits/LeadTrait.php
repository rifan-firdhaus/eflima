<?php namespace modules\crm\migrations\dummy\traits;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Faker\Factory;
use modules\account\models\Staff;
use modules\address\models\Country;
use modules\crm\models\Customer;
use modules\crm\models\Lead;
use modules\crm\models\LeadSource;
use modules\crm\models\LeadStatus;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait LeadTrait
{
    public function createLead()
    {
        $faker = Factory::create();

        $staffTotal = Staff::find()->count();

        $model = new Lead([
            'scenario' => 'admin/add',
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'phone' => $faker->phoneNumber,
            'mobile' => $faker->phoneNumber,
            'email' => $faker->email,
            'status_id' => LeadStatus::find()
                ->enabled()
                ->orderBy('RAND()')
                ->select('id')
                ->createCommand()
                ->queryScalar(),
            'source_id' => LeadSource::find()
                ->enabled()->orderBy('RAND()')
                ->select('id')
                ->createCommand()
                ->queryScalar(),
            'city' => $faker->city,
            'province' => $faker->state,
            'address' => $faker->streetAddress,
            'postal_code' => $faker->postcode,
            'country_code' => Country::find()
                ->orderBy('RAND()')
                ->enabled()
                ->select('code')
                ->createCommand()
                ->queryScalar(),
            'assignor_id' => Staff::root()->id,
            'assignee_ids' => Staff::find()
                ->select('id')
                ->orderBy('RAND()')
                ->limit(rand(1, $staffTotal))
                ->createCommand()
                ->queryColumn()
        ]);

        if (!$model->save()) {
            return false;
        }

        return true;
    }
}
