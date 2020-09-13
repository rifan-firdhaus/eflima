<?php namespace modules\content\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\content\components\PostType;
use modules\content\Content;
use modules\content\models\queries\PostQuery;
use modules\content\models\queries\PostTaxonomyRelationshipQuery;
use modules\core\behaviors\AttributeTypecastBehavior;
use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\file_manager\behaviors\FileUploaderBehavior;
use modules\file_manager\helpers\ImageVersion;
use modules\file_manager\web\UploadedFile;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * - FileUploaderBehavior methods
 * @method bool uploadFile(string $attribute)
 * @method bool deleteFile(string $attribute, string | null $fileName = null)
 * @method string|bool getFilePath(string $attribute, string | null $fileName = null)
 * @method getFileUrl(string $attribute, bool | string $scheme = true, string | null $fileName = null)
 * @method bool|string getFileVersionPath(string $attribute, string $version = 'original')
 * @method bool|string getFileVersionUrl(string $attribute, string $version = 'original', string | bool $scheme = true)
 * @method array getFileMetadata(string $attribute, string | null $fileName = null)
 * @method string getFileAttributeByAlias(string $alias)
 *
 * - Getter
 * @property PostTaxonomyRelationship[] $taxonomyRelationships
 * @property PostType                   $type
 * @property int                        $id              [int(10) unsigned]
 * @property string                     $title
 * @property string                     $slug
 * @property string                     $content
 * @property string                     $type_id         [varchar(36)]
 * @property bool                       $is_published    [tinyint(1)]
 * @property string                     $picture
 * @property int                        $published_at    [int(11) unsigned]
 * @property int                        $created_at      [int(11) unsigned]
 * @property int                        $updated_at      [int(11) unsigned]
 */
class Post extends ActiveRecord
{
    /** @var UploadedFile */
    public $uploaded_picture;

    /** @var PostType */
    protected $_type;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%post}}';
    }

    /**
     * @inheritdoc
     * @return PostQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new PostQuery(get_called_class());

        return $query->alias("post");
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'required'],
            [['is_published'], 'boolean'],
            [
                'uploaded_picture',
                'image',
                'maxSize' => 8 * 1024 * 1024,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
        ];

        $behaviors['fileUploader'] = [
            'class' => FileUploaderBehavior::class,
            'attributes' => [
                'picture' => [
                    'alias' => 'uploaded_picture',
                    'base_path' => '@webroot/protected/system/post/picture',
                    'base_url' => '@web/protected/system/post/picture',
                ],
            ],
        ];

        $behaviors['sluggable'] = [
            'class' => SluggableBehavior::class,
            'attribute' => 'title',
            'ensureUnique' => true,
        ];

        $behaviors['attributeTypecast'] = [
            'class' => AttributeTypecastBehavior::class,
            'attributeTypes' => [
                'id' => AttributeTypecastBehavior::TYPE_INTEGER,
                'is_published' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                'created_at' => AttributeTypecastBehavior::TYPE_INTEGER,
                'updated_at' => AttributeTypecastBehavior::TYPE_INTEGER,
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['admin/add'] = $scenarios['default'];
        $scenarios['admin/update'] = $scenarios['default'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'slug' => Yii::t('app', 'Slug'),
            'content' => Yii::t('app', 'Content'),
            'type' => Yii::t('app', 'Type'),
            'is_published' => Yii::t('app', 'Published'),
            'picture' => Yii::t('app', 'Picture'),
            'uploaded_picture' => Yii::t('app', 'Picture'),
            'published_at' => Yii::t('app', 'Published At'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery|PostTaxonomyRelationshipQuery
     */
    public function getTaxonomyRelationships()
    {
        return $this->hasMany(PostTaxonomyRelationship::class, ['post_id' => 'id']);
    }

    /**
     * @return PostType
     */
    public function getType()
    {
        if (!isset($this->_type)) {
            /** @var Content $module */
            $module = Yii::$app->getModule('content');

            $this->_type = $module->getPostType($this->type_id);
        }

        return $this->_type;
    }

    /**
     * @param int $publish
     *
     * @return bool
     */
    public function publish($publish = 1)
    {
        if (!$publish) {
            return $this->unpublish();
        }

        if ($this->is_published) {
            return true;
        }

        $this->is_published = true;
        $this->published_at = time();

        return $this->save(false);
    }

    /**
     * @return bool
     */
    public function unpublish()
    {
        if (!$this->is_published) {
            return true;
        }

        $this->is_published = false;
        $this->published_at = null;

        return $this->save(false);
    }

    /**
     * @inheritDoc
     */
    public function fields()
    {
        $fields = parent::fields();

        unset($fields['content']);

        return ArrayHelper::merge($fields, [
            'picture' => function ($model) {
                /** @var Post $model */

                return $model->getFileVersionUrl('picture');
            },
        ]);
    }

    /**
     * @inheritDoc
     */
    public function extraFields()
    {
        $fields = ArrayHelper::merge(parent::extraFields(), [
            'content' => 'content',
            'shortContent' => function ($model) {
                /** @var Post $model */

                return StringHelper::truncateWords($model->content, 259);
            },
        ]);

        foreach (ImageVersion::instance()->getVersions() AS $version) {
            $fields["picture_{$version}"] = function ($model) use ($version) {
                /** @var Post $model */

                return $model->getFileVersionUrl('picture', $version);
            };
        }

        return $fields;
    }
}
