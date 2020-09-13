<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

use yii\helpers\Inflector; ?><?= "<?php "; ?> namespace <?= $generator->ns ?>;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use Yii;

/**
* @author Rifan Firdhaus Widigdo
<rifanfirdhaus@gmail.com>
*
<?php if (!empty($relations)): ?>
  *
    <?php foreach ($relations as $name => $relation): ?>
    * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
    <?php endforeach; ?>
<?php endif; ?>
*/
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
/**
* @inheritdoc
*/
public static function tableName()
{
return '<?= $generator->generateTableName($tableName) ?>';
}
<?php if ($generator->db !== 'db'): ?>

  /**
  * @return \yii\db\Connection the database connection used by this AR class.
  */
  public static function getDb()
  {
  return Yii::$app->get('<?= $generator->db ?>');
  }
<?php endif; ?>

/**
* @inheritdoc
*/
public function rules()
{
return [<?= empty($rules) ? '' : ("\n            " . implode(",\n            ", $rules) . ",\n        ") ?>];
}

/**
* @inheritdoc
*/
public function attributeLabels()
{
return [
<?php foreach ($labels as $name => $label): ?>
    <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
];
}
<?php foreach ($relations as $name => $relation): ?>

  /**
  * @return \modules\core\db\ActiveQuery
  */
  public function get<?= $name ?>()
  {
    <?= $relation[0] . "\n" ?>
  }
<?php endforeach; ?>
<?php if ($queryClassName): ?>
    <?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
    ?>
  /**
  * @inheritdoc
  * @return <?= $queryClassFullName ?> the active query used by this AR class.
  */
  public static function find()
  {
  $query = new <?= $queryClassFullName ?>(get_called_class());

  return $query->alias("<?= Inflector::underscore($className) ?>");
  }
<?php else: ?>

  /**
  * @inheritdoc
  */
  public static function find()
  {
  return parent::find()->alias("<?= $tableName ?>");
  }
<?php endif; ?>
}
