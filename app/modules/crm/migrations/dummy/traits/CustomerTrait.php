<?php namespace modules\crm\migrations\dummy\traits;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Faker\Factory;
use modules\address\models\Country;
use modules\crm\models\Customer;
use modules\crm\models\CustomerContact;
use modules\crm\models\CustomerContactAccount;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait CustomerTrait
{
    public function createCustomer()
    {
        $faker = Factory::create();
        $types = array_keys(Customer::types());

        $model = new Customer([
            'type' => $types[array_rand($types)],
        ]);

        if ($model->type === Customer::TYPE_COMPANY) {
            $model->setAttributes([
                'company_name' => $faker->company,
                'country_code' => Country::find()
                    ->orderBy('RAND()')
                    ->enabled()->select('code')
                    ->createCommand()
                    ->queryScalar(),
                'email' => $faker->email,
                'phone' => $faker->phoneNumber,
                'vat_number' => $faker->randomNumber(7),
                'city' => $faker->city,
                'postal_code' => $faker->postcode,
                'province' => $faker->state,
                'address' => $faker->streetAddress,
            ]);
        }

        $model->primaryContactModel = $this->createCustomerContact(null, false);

        if (!$model->save()) {
            return false;
        }

        return $model;
    }

    public function createCustomerContact($customerId = null, $save = true)
    {
        $faker = Factory::create();

        $model = new CustomerContact([
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'phone' => $faker->phoneNumber,
            'mobile' => $faker->phoneNumber,
            'email' => $faker->email,
            'city' => $faker->city,
            'country_code' => Country::find()
                ->orderBy('RAND()')
                ->enabled()
                ->select('code')
                ->createCommand()
                ->queryScalar(),
            'address' => $faker->streetAddress,
            'province' => $faker->state,
            'postal_code' => $faker->postcode,
            'has_customer_area_access' => true,
        ]);

        $model->accountModel = new CustomerContactAccount([
            'password' => 'rifan1234',
            'password_repeat' => 'rifan1234',
        ]);

        if ($customerId) {
            $model->customer_id = $customerId;
        }

        if ($save) {
            return $model->save();
        }

        return $model;
    }
}
