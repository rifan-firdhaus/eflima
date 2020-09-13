<?php namespace modules\account\models\queries;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class AccountQuery extends ActiveQuery
{
    /**
     * @param string $username
     *
     * @return $this
     */
    public function username($username)
    {
        return $this->andWhere(["{$this->getAlias()}.username" => $username]);
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function email($email)
    {
        return $this->andWhere(["{$this->getAlias()}.email" => $email]);
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function passwordReset($key)
    {
        return $this->andWhere(["{$this->getAlias()}.password_reset_token" => $key])
            ->andWhere(['>=', "{$this->getAlias()}.password_reset_token_expiration", time()]);
    }

    /**
     * @param bool $blocked
     *
     * @return $this
     */
    public function blocked($blocked = true)
    {
        return $this->andWhere(["{$this->getAlias()}.is_blocked" => (boolean) $blocked]);
    }

    /**
     * @param bool $confirmed
     *
     * @return $this
     */
    public function confirmed($confirmed = true)
    {
        if (!$confirmed) {
            return $this->andWhere(["{$this->getAlias()}.is_confirmed" => null]);
        }

        return $this->andWhere(['IS_NOT', "{$this->getAlias()}.confirmed_at", null]);
    }

    /**
     * @param $type
     *
     * @return $this
     */
    public function type($type)
    {
        return $this->andWhere(["{$this->getAlias()}.type" => $type]);
    }
}