<?php namespace modules\core\models\traits;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
trait VisibilityModel
{
    /**
     * @param int $enable
     *
     * @return bool
     */
    public function enable($enable = 1)
    {
        if (!$enable) {
            return $this->disable();
        }

        if ($this->is_enabled) {
            return true;
        }

        $this->is_enabled = true;

        return $this->save(false);
    }

    /**
     * @return bool
     */
    public function disable()
    {
        if (!$this->is_enabled) {
            return true;
        }

        $this->is_enabled = false;

        return $this->save(false);
    }
}