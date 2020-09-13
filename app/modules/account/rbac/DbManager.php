<?php namespace modules\account\rbac;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\Role;
use yii\base\Exception;
use yii\db\Query;
use yii\rbac\DbManager as BaseDbManager;
use yii\rbac\Item;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property array|Role[] $rootRoles
 */
class DbManager extends BaseDbManager
{
    /**
     * @param array $permissions
     *
     * @return bool
     * @throws Exception
     */
    public function installPermissions($permissions)
    {
        foreach ($permissions AS $name => $config) {
            $permission = $this->createPermission($name);
            $permission->description = isset($config['description']) ? $config['description'] : null;

            if (!$this->add($permission)) {
                return false;
            }

            if (isset($config['parent'])) {
                $parentPermission = $this->getPermission($config['parent']);

                if (!$parentPermission) {
                    return false;
                }

                if (!$this->addChild($parentPermission, $permission)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param array $permissions
     *
     * @return bool
     */
    public function uninstallPermissions($permissions)
    {
        $permissions = array_reverse($permissions, true);

        foreach ($permissions AS $name => $config) {
            $permission = $this->getPermission($name);

            if (!$permission) {
                continue;
            }

            if (!$this->remove($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $childRole
     *
     * @return null|Role|Item
     */
    public function getParentRole($childRole)
    {
        $row = (new Query())->from(['role' => $this->itemTable])
            ->select(['role.*'])
            ->join('LEFT JOIN', "{$this->itemChildTable} AS child_via", "child_via.parent = role.name")
            ->join('LEFT JOIN', "{$this->itemTable} AS child", "child.name = child_via.child AND child.type = " . Item::TYPE_ROLE)
            ->andWhere(['child_via.child' => $childRole, 'role.type' => Item::TYPE_ROLE])
            ->one($this->db);

        if ($row === false) {
            return null;
        }

        return $this->populateItem($row);
    }

    /**
     * @return array|Role[]
     */
    public function getRootRoles()
    {
        $rows = (new Query())->from(['role' => $this->itemTable])
            ->select(['role.*'])
            ->join('LEFT JOIN', "{$this->itemChildTable} AS parent_via", "parent_via.child = role.name")
            ->join('LEFT JOIN', "{$this->itemTable} AS parent", "parent_via.parent = parent.name AND parent.type=" . Item::TYPE_ROLE)
            ->andWhere(['role.type' => Item::TYPE_ROLE, 'parent.name' => null])
            ->all($this->db);
        $roles = [];

        foreach ($rows AS $index => $row) {
            $roles[$index] = $this->populateItem($row);
        }

        return $roles;
    }

    public function getDirectChildRoles($parentRole)
    {
        $rows = (new Query())->from(['role' => $this->itemTable])
            ->select(['role.*'])
            ->join('LEFT JOIN', "{$this->itemChildTable} AS parent_via", "parent_via.child = role.name")
            ->join('LEFT JOIN', "{$this->itemTable} AS parent", "parent_via.parent = parent.name AND parent.type=" . Item::TYPE_ROLE)
            ->andWhere(['role.type' => Item::TYPE_ROLE, 'parent.name' => $parentRole])
            ->all($this->db);
        $roles = [];

        foreach ($rows AS $index => $row) {
            $roles[$index] = $this->populateItem($row);
        }

        return $roles;
    }
}