<?php namespace modules\account\migrations\dummy\traits;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Faker\Factory;
use modules\account\models\AccountContact;
use modules\account\models\Staff;
use modules\account\models\StaffAccount;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait StaffTrait
{
    public function createStaff()
    {
        $faker = Factory::create();
        $roles = Yii::$app->authManager->getRoles();

        $model = new Staff([
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
        ]);

        $model->accountModel = new StaffAccount([
            'email' => $faker->email,
            'username' => $faker->userName,
            'password' => 'rifan1234',
            'password_repeat' => 'rifan1234',
            'role' => $roles[array_rand($roles)]->name
        ]);

        $model->accountModel->contactModel = new AccountContact([
            'address' => $faker->address,
            'phone' => $faker->phoneNumber
        ]);

        if (!$model->save()) {
            return false;
        }

        return true;
    }
}
