<?php namespace modules\account\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\ui\widgets\inputs\Select2Input;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class RoleInput extends Select2Input
{

    /**
     * @inheritdoc
     */
    public function normalize()
    {
        $roles = Yii::$app->authManager->getRoles();

        foreach ($roles AS $role) {
            $this->source[$role->name] = $role->description;
        }
    }

}
