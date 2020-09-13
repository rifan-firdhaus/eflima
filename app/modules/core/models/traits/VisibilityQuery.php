<?php namespace modules\core\models\traits;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait VisibilityQuery
{
    /**
     * @return $this
     */
    public function enabled()
    {
        return $this->andWhere(["{$this->getAlias()}.is_enabled" => true]);
    }

    /**
     * @return $this
     */
    public function disabled()
    {
        return $this->andWhere(["{$this->getAlias()}.is_enabled" => false]);
    }
}