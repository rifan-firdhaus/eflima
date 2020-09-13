<?php namespace modules\core\db;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\db\ActiveQuery as BaseActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ActiveQuery extends BaseActiveQuery
{
    public function map($key, $value, $group = null)
    {
        return ArrayHelper::map($this->all(), $key, $value, $group);
    }

    protected function getAlias()
    {

        if (!empty($this->from)) {
            foreach ($this->from as $alias => $tableName) {
                if (is_string($alias)) {
                    return $alias;
                }
                break;
            }
        }

        $tableName = $this->getPrimaryTableName();

        if (preg_match('/^(.*?)\s+({{\w+}}|\w+)$/', $tableName, $matches)) {
            return $matches[2];
        }

        return $tableName;
    }
}