<?php namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\account\rbac\DbManager;
use Throwable;
use Yii;
use yii\base\Exception as YiiException;
use yii\base\Model;
use yii\helpers\Inflector;
use yii\rbac\ManagerInterface;
use yii\rbac\Permission as RbacPermission;
use yii\rbac\Role as RbacRole;

/** @noinspection PropertiesInspection */

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property RbacPermission|null $parent
 */
class Permission extends Model
{
    public $parent_name;
    public $name;
    public $description;
    public $isNewRecord = true;
    protected $_parent;

    /**
     * @return Permission[]|array
     */
    public static function all()
    {
        $permissions = self::getAuthManager()->getPermissions();
        $models = [];

        foreacH ($permissions AS $index => $permission) {
            $models[$index] = self::toModel($permission);
        }

        return $models;
    }

    /**
     * @param string|RbacPermission $permission
     *
     * @return Permission|null
     */
    public static function toModel($permission)
    {
        if (!($permission instanceof RbacPermission)) {
            $permission = self::getAuthManager()->getPermission($permission);

            if (!$permission) {
                return null;
            }
        }

        $model = new self([
            'name' => $permission->name,
            'description' => $permission->description,
            'isNewRecord' => false,
        ]);

        if ($model->getParent()) {
            $model->parent_name = $model->getParent()->name;
        }

        return $model;
    }

    /**
     * @return null|RbacPermission
     */
    public function getParent()
    {
        if (!$this->_parent) {
            $this->_parent = self::getAuthManager()->getParentPermission($this->name);
        }

        return $this->_parent;
    }

    /**
     * @param RbacRole $role
     * @param bool     $deep
     *
     * @return bool
     */
    public function isAllowed($role, $deep = true)
    {
        $authManager = Yii::$app->authManager;

        $permissionRbac = $authManager->getPermission($this->name);

        if ($authManager->hasChild($role, $permissionRbac)) {
            return true;
        }

        if ($deep && $this->parent) {
            return self::toModel($this->parent)->isAllowed($role);
        }

        return false;
    }

    /**
     * @param string|RbacRole $role
     *
     * @return array
     */
    public static function tree($role)
    {
        if (!($role instanceof RbacRole)) {
            $role = self::getAuthManager()->getRole($role);
        }

        $roots = self::getAuthManager()->getRootPermissions();
        $result = [];

        foreach ($roots AS $root) {
            $result[$root->name] = self::branches($root, $role);
            $result[$root->name]['has_access'] = self::toModel($root)->isAllowed($role);
        }

        return $result;
    }

    /**
     * @param RbacPermission $permission
     * @param RbacRole       $role
     *
     * @return array
     */
    protected static function branches($permission, $role)
    {
        $auth = self::getAuthManager();
        $children = $auth->getDirectChildPermissions($permission->name);

        $result = [
            'permission' => self::toModel($permission),
            'children' => [],
        ];

        foreach ($children AS $index => $child) {
            $result['children'][$index] = self::branches($child, $role);
            $result['children'][$index]['has_access'] = self::toModel($child)->isAllowed($role);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'required'],
            ['parent_name', 'validateParent'],
        ];
    }

    /**
     * @param string $attribute
     */
    public function validateParent($attribute)
    {
        if ($this->hasErrors()) {
            return;
        }

        if (!self::getAuthManager()->getPermission($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Parent permission doesn\'t exists'));
        }
    }

    /**
     * @return DbManager|ManagerInterface
     */
    public static function getAuthManager()
    {
        return Yii::$app->getAuthManager();
    }

    /**
     * @return bool
     *
     * @throws Exception
     * @throws Throwable
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $auth = self::getAuthManager();

        $transaction = $auth->db->beginTransaction();

        try {
            if (empty($this->name)) {
                $this->name = Inflector::underscore($this->description);
            }

            $permission = $this->isNewRecord ? $auth->createPermission($this->name) : $auth->getPermission($this->name);
            $permission->description = $this->description;
            $isUpdated = $this->isNewRecord ? $auth->add($permission) : $auth->update($this->name, $permission);

            if (!$isUpdated) {
                $transaction->rollBack();

                return false;
            }

            if ($this->parent_name && ($this->isNewRecord || $this->parent->name != $this->parent_name)) {
                $parent = $auth->getPermission($this->parent_name);

                if (!$auth->canAddChild($parent, $permission) || !$auth->addChild($parent, $permission)) {
                    $transaction->rollBack();

                    return false;
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $auth = self::getAuthManager();
        $permission = $auth->getPermission($this->name);

        if (!$permission) {
            return false;
        }

        return $auth->remove($permission);
    }

    /**
     * @param RbacRole $role
     *
     * @return bool
     */
    public function _allowParents($role)
    {
        if (!$this->parent) {
            return true;
        }

        $authManager = self::getAuthManager();

        $totalAllowedPermissions = $authManager->getAllowedDirectChildPermissionCount($role->name, $this->parent->name);
        $totalChildPermissions = $authManager->getDirectChildPermissionsCount($this->parent->name);

        if ($totalAllowedPermissions == $totalChildPermissions) {
            $parent = self::toModel($this->parent);

            if (!$parent->allow($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param RbacRole|string $role
     *
     * @return bool
     * @throws Throwable
     */
    public function allow($role)
    {
        $authManager = self::getAuthManager();
        $permision = $authManager->getPermission($this->name);

        if (!($role instanceof RbacRole)) {
            $role = self::getAuthManager()->getRole($role);
        }

        if (
            !$role ||
            $this->isAllowed($role) ||
            !$authManager->canAddChild($role, $permision)
        ) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$authManager->addChild($role, $permision)) {
                $transaction->rollBack();

                return false;
            }

            if (!$this->_revokeChildren($role)) {
                $transaction->rollBack();

                return false;
            }

            if (!$this->_allowParents($role)) {
                $transaction->rollBack();

                return false;
            }

            $transaction->commit();

            return true;
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    /**
     * @param RbacRole $role
     *
     * @return bool
     */
    public function _revokeChildren($role)
    {
        $authManager = self::getAuthManager();

        foreach ($authManager->getDirectChildPermissions($this->name) AS $child) {
            $childPermission = self::toModel($child);

            if (!$childPermission->_revokeChildren($role)) {
                return false;
            }

            if ($authManager->hasChild($role, $child) && !$authManager->removeChild($role, $child)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param RbacRole $role
     *
     * @return bool
     * @throws YiiException
     */
    public function _revokeParents($role)
    {
        $authManager = self::getAuthManager();

        foreach ($authManager->getDirectChildPermissions($this->name) AS $child) {
            if ($child->name != $this->name) {
                if (!$authManager->addChild($role, $child)) {
                    return false;
                }
            }
        }

        if ($this->parent) {
            $hasParentPermission = $authManager->hasChild($role, $this->parent);

            $parentPermission = self::toModel($this->parent);

            if ($hasParentPermission && !$parentPermission->_revokeParents($role)) {
                return false;
            }

            if (!$authManager->removeChild($role, $authManager->getPermission($this->name))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string|RbacRole $role
     *
     * @return bool
     * @throws Throwable
     */
    public function deny($role)
    {
        $authManager = self::getAuthManager();
        $permission = $authManager->getPermission($this->name);

        if (!($role instanceof RbacRole)) {
            $role = self::getAuthManager()->getRole($role);
        }

        if (
            !$role ||
            !$this->isAllowed($role)
        ) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$this->isAllowed($role, false)) {
                $parentPermission = self::toModel($this->parent);

                if (!$parentPermission->_revokeParents($role)) {
                    $transaction->rollBack();

                    return false;
                }
            }

            if (!$authManager->removeChild($role, $permission)) {
                $transaction->rollBack();

                return false;
            }


            $transaction->commit();

            return true;
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    /**
     * @param RbacRole|string $role
     *
     * @return bool
     * @throws Throwable
     */
    public function toggle($role)
    {
        $authManager = self::getAuthManager();

        if (!($role instanceof RbacRole)) {
            $role = $authManager->getRole($role);
        }

        if ($this->isAllowed($role)) {
            return $this->deny($role);
        }

        return $this->allow($role);
    }
}
