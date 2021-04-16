<?php namespace modules\crm\controllers\customer;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\crm\models\forms\customer_contact_account\CustomerContactAccountLogin;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class CustomerController extends Controller
{
    public function actionLogin()
    {
        $model = new CustomerContactAccountLogin();

        return $this->render('login', compact('model'));
    }
}
