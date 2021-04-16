<?php namespace modules\account\rbac;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\account\models\Role;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\rbac\DbManager as BaseDbManager;
use yii\rbac\Item;
use yii\rbac\Permission as RbacPermission;
use yii\rbac\Role as RbacRole;

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
                throw new InvalidConfigException("Failed to add permission {$name}");
            }

            if (isset($config['parent'])) {
                $parentPermission = $this->getPermission($config['parent']);

                if (!$parentPermission) {
                    throw new InvalidConfigException("Cant find parent permission {$config['parent']}");
                }

                if (!$this->addChild($parentPermission, $permission)) {
                    throw new InvalidConfigException("Failed to add permission {$name} to {$config['parent']}");
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
     * @return null|RbacRole|Item
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
     * @return RbacRole[]
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

    /**
     * @param string $parentRole
     *
     * @return RbacRole[]
     */
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

    /**
     * @param $childPermission
     *
     * @return null|RbacPermission|Item
     */
    public function getParentPermission($childPermission)
    {
        $row = (new Query())->from(['permission' => $this->itemTable])
            ->select(['permission.*'])
            ->join('LEFT JOIN', "{$this->itemChildTable} AS child_via", "child_via.parent = permission.name")
            ->join('LEFT JOIN', "{$this->itemTable} AS child", "child.name = child_via.child AND child.type = " . Item::TYPE_PERMISSION)
            ->andWhere(['child_via.child' => $childPermission, 'permission.type' => Item::TYPE_PERMISSION])
            ->one($this->db);

        if ($row === false) {
            return null;
        }

        return $this->populateItem($row);
    }

    /**
     * @return array|RbacPermission[]
     */
    public function getRootPermissions()
    {
        $sql = (new Query())->from(['permission' => $this->itemTable])
            ->select(['permission.name'])
            ->join('LEFT JOIN', "{$this->itemChildTable} AS parent_via", "parent_via.child = permission.name")
            ->join('LEFT JOIN', "{$this->itemTable} AS parent", "parent_via.parent = parent.name")
            ->andWhere([
                'permission.type' => Item::TYPE_PERMISSION,
                'parent.type' => Item::TYPE_PERMISSION,
            ]);

        $rows = (new Query())
            ->from(['permission' => $this->itemTable])
            ->andWhere(['NOT IN', 'permission.name', $sql])
            ->orderBy('permission.order')
            ->andWhere(['permission.type' => Item::TYPE_PERMISSION])
            ->all();

        $permissions = [];

        foreach ($rows AS $index => $row) {
            $permissions[$index] = $this->populateItem($row);
        }

        return $permissions;
    }

    /**
     * @param string $parentPermission
     *
     * @return RbacPermission[]
     */
    public function getDirectChildPermissions($parentPermission)
    {
        $rows = (new Query())->from(['permission' => $this->itemTable])
            ->select(['permission.*'])
            ->orderBy('permission.order')
            ->join('LEFT JOIN', "{$this->itemChildTable} AS parent_via", "parent_via.child = permission.name")
            ->join('LEFT JOIN', "{$this->itemTable} AS parent", "parent_via.parent = parent.name AND parent.type=" . Item::TYPE_PERMISSION)
            ->andWhere(['permission.type' => Item::TYPE_PERMISSION, 'parent.name' => $parentPermission])
            ->all($this->db);
        $permissions = [];

        foreach ($rows AS $index => $row) {
            $permissions[$index] = $this->populateItem($row);
        }

        return $permissions;
    }

    /**
     * @param string $parentPermission
     *
     * @return string|int
     */
    public function getDirectChildPermissionsCount($parentPermission)
    {
        return (new Query())->from(['permission' => $this->itemTable])
            ->select(['permission.*'])
            ->join('LEFT JOIN', "{$this->itemChildTable} AS parent_via", "parent_via.child = permission.name")
            ->join('LEFT JOIN', "{$this->itemTable} AS parent", "parent_via.parent = parent.name AND parent.type=" . Item::TYPE_PERMISSION)
            ->andWhere(['permission.type' => Item::TYPE_PERMISSION, 'parent.name' => $parentPermission])
            ->count();
    }

    public function getAllowedDirectChildPermissionCount($role, $parentPermission)
    {
        $childrenPermission = ArrayHelper::map($this->getDirectChildPermissions($parentPermission), 'name', 'name');

        return (new Query())->from(['role_permission' => $this->itemChildTable])
            ->andWhere(['role_permission.parent' => $role, 'role_permission.child' => $childrenPermission])
            ->join('LEFT JOIN', "{$this->itemTable} AS permission", "role_permission.child = permission.name")
            ->andWhere(['permission.type' => Item::TYPE_PERMISSION])
            ->count();
    }


    /**
     * {@inheritdoc}
     */
    protected function addItem($item)
    {
        $time = time();
        $permissionType = Item::TYPE_PERMISSION;
        if ($item->createdAt === null) {
            $item->createdAt = $time;
        }
        if ($item->updatedAt === null) {
            $item->updatedAt = $time;
        }
        $this->db->createCommand()
            ->insert($this->itemTable, [
                'name' => $item->name,
                'type' => $item->type,
                'order' => $this->db->createCommand("SELECT COUNT(*) FROM {$this->itemTable} AS permission WHERE permission.type = {$permissionType}")->queryScalar(),
                'description' => $item->description,
                'rule_name' => $item->ruleName,
                'data' => $item->data === null ? null : serialize($item->data),
                'created_at' => $item->createdAt,
                'updated_at' => $item->updatedAt,
            ])->execute();

        $this->invalidateCache();

        return true;
    }
}
