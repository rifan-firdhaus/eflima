<?php namespace modules\account\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Exception;
use modules\account\rbac\DbManager;
use Throwable;
use Yii;
use yii\base\Model;
use yii\helpers\Inflector;
use yii\rbac\ManagerInterface;
use yii\rbac\Role as RbacRole;

/** @noinspection PropertiesInspection */

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property RbacRole|null $parent
 */
class Role extends Model
{
    public $parent_name;
    public $name;
    public $description;
    public $isNewRecord = true;
    protected $_parent;

    /**
     * @return Role[]|array
     */
    public static function all()
    {
        $roles = self::getAuthManager()->getRoles();
        $models = [];

        foreacH ($roles AS $index => $role) {
            $models[$index] = self::find($role);
        }

        return $models;
    }

    /**
     * @param string $role
     *
     * @return Role|null
     */
    public static function find($role)
    {
        if (!($role instanceof RbacRole)) {
            $role = self::getAuthManager()->getRole($role);

            if (!$role) {
                return null;
            }
        }

        $model = new self([
            'name' => $role->name,
            'description' => $role->description,
            'isNewRecord' => false,
        ]);

        if ($model->getParent()) {
            $model->parent_name = $model->getParent()->name;
        }

        return $model;
    }

    /**
     * @return null|RbacRole
     */
    public function getParent()
    {
        if (!$this->_parent) {
            $this->_parent = self::getAuthManager()->getParentRole($this->name);
        }

        return $this->_parent;
    }

    /**
     * @return array
     */
    public static function tree()
    {
        $roots = self::getAuthManager()->getRootRoles();
        $result = [];

        foreach ($roots AS $root) {
            $result[$root->name] = self::branches($root);
        }

        return $result;
    }

    protected static function branches($role)
    {
        $auth = self::getAuthManager();
        $children = $auth->getDirectChildRoles($role->name);

        $result = [
            'role' => self::find($role),
            'children' => [],
        ];

        foreach ($children AS $index => $child) {
            $result['children'][$index] = self::branches($child);
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

        if (!self::getAuthManager()->getRole($this->{$attribute})) {
            $this->addError($attribute, Yii::t('app', 'Parent role doesn\'t exists'));
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

            $role = $this->isNewRecord ? $auth->createRole($this->name) : $auth->getRole($this->name);
            $role->description = $this->description;
            $isUpdated = $this->isNewRecord ? $auth->add($role) : $auth->update($this->name, $role);

            if (!$isUpdated) {
                $transaction->rollBack();

                return false;
            }

            if ($this->parent_name && ($this->isNewRecord || $this->parent->name != $this->parent_name)) {
                $parent = $auth->getRole($this->parent_name);

                if (!$auth->canAddChild($parent, $role) || !$auth->addChild($parent, $role)) {
                    $transaction->rollBack();

                    return false;
                }
            }
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        $transaction->commit();

        return true;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $auth = self::getAuthManager();
        $role = $auth->getRole($this->name);

        if (!$role) {
            return false;
        }

        return $auth->remove($role);
    }
}
