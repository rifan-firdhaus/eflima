<?php namespace modules\file_manager\behaviors;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use modules\core\db\ActiveRecord;
use modules\file_manager\helpers\ImageVersion;
use modules\file_manager\web\UploadedFile;
use Yii;
use yii\base\Behavior;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\ModelEvent;
use yii\helpers\FileHelper;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class FileUploaderBehavior extends Behavior
{
    public $attributes = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * @param ModelEvent $event
     */
    public function beforeSave($event)
    {
        foreach ($this->attributes AS $attributeName => $config) {
            if (!$this->isUploadableAttribute($attributeName)) {
                continue;
            }

            $filePath = $this->uploadFile($attributeName);

            if ($filePath === false) {
                $event->isValid = false;

                break;
            }

            if ($filePath) {
                if ($this->owner->{$attributeName}) {
                    $this->deleteFile($attributeName);
                }

                $this->owner->{$attributeName} = basename($filePath);
            }
        }
    }

    /**
     * @param string $attribute
     *
     * @return bool
     */
    protected function isUploadableAttribute($attribute)
    {
        $config = $this->getConfig($attribute);

        if (!isset($config['is_file']) || $config['is_file'] === true) {
            return true;
        }

        if ($config['is_file'] instanceof Closure) {
            return call_user_func($config['is_file'], $this->owner);
        }

        return false;
    }

    /**
     * @param string $attribute
     *
     * @return array
     */
    protected function getConfig($attribute)
    {
        return $this->attributes[$attribute];
    }

    /**
     * @param string $attribute
     *
     * @return void|false|string
     */
    public function uploadFile($attribute)
    {
        $config = $this->getConfig($attribute);
        $alias = $config['alias'];
        $file = $this->owner->{$alias};

        if (!($file instanceof UploadedFile)) {
            return;
        }

        $filePath = $file->saveAs($this->getFilePath($attribute, $file->name));

        if ($filePath === false) {
            return false;
        }

        return $filePath;
    }

    /**
     * @param string      $attribute
     * @param string|null $fileName
     *
     * @return bool|string
     */
    public function getFilePath($attribute, $fileName = null)
    {
        $config = $this->getConfig($attribute);

        if ($fileName === null) {
            $fileName = $this->owner->{$attribute};
        }

        if (empty($fileName)) {
            return false;
        }

        return Yii::getAlias($config['base_path'] . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * @param string      $attribute
     * @param string|null $fileName
     *
     * @return bool|string
     */
    public function deleteFile($attribute, $fileName = null)
    {
        if ($fileName === null) {
            $fileName = $this->owner->{$attribute};
        }

        $file = $this->getFilePath($attribute, $fileName);

        if (file_exists($file) && is_file($file)) {
            unlink($file);
        }
    }

    /**
     * @param ModelEvent $event
     */
    public function afterDelete($event)
    {
        foreach ($this->attributes AS $attributeName => $config) {
            if ($this->isUploadableAttribute($attributeName) && $this->owner->{$attributeName}) {
                $this->deleteFile($attributeName);
            }
        }
    }

    /**
     * @param $alias
     *
     * @return bool|string
     */
    public function getFileAttributeByAlias($alias)
    {
        foreach ($this->attributes AS $attributeName => $config) {
            if ($config['alias'] === $alias) {
                return $attributeName;
            }
        }

        return false;
    }

    /**
     * @param string $attribute
     * @param string $version
     *
     * @return bool|string
     *
     * @throws Exception
     */
    public function getFileVersionPath($attribute, $version = 'original')
    {
        $file = $this->getFilePath($attribute);

        if ($version === 'original') {
            return $file;
        }

        if (!ImageVersion::isImage($file)) {
            return $file;
        }

        return ImageVersion::instance()->path($version, $file);
    }

    /**
     * @param string $attribute
     * @param string $fileName
     *
     * @return array
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function getFileMetadata($attribute, $fileName = null)
    {
        if ($fileName === null) {
            $fileName = $this->owner->{$attribute};
        }

        $file = $this->getFilePath($attribute, $fileName);

        if (!file_exists($file)) {
            return null;
        }

        return [
            'size' => filesize($file),
            'name' => basename($file),
            'type' => FileHelper::getMimeType($file),
            'src' => $this->getFileVersionUrl($attribute, 'thumbnail'),
            'url' => $this->getFileUrl($attribute),
        ];
    }

    /**
     * @param string      $attribute
     * @param string      $version
     * @param null|string $default
     * @param bool|string $scheme
     *
     * @return bool|string
     *
     * @throws Exception
     */
    public function getFileVersionUrl($attribute, $version = 'original', $default = null, $scheme = true)
    {
        if ($version === 'original') {
            $file = $this->getFileUrl($attribute, $scheme);

            if (!$file) {
                return $default;
            }
        } else {
            $path = $this->getFilePath($attribute);

            if (!$path) {
                return $default;
            }

            if (!ImageVersion::isImage($path)) {
                return $this->getFileUrl($attribute, $scheme);
            }

            $file = ImageVersion::instance()->url($version, $path, $scheme);
        }

        return $file;
    }

    /**
     * @param string      $attribute
     * @param string|null $fileName
     * @param string|true $scheme
     *
     * @return bool|string
     */
    public function getFileUrl($attribute, $scheme = true, $fileName = null)
    {
        $config = $this->getConfig($attribute);

        if ($fileName === null) {
            $fileName = $this->owner->{$attribute};
        }

        if (empty($fileName)) {
            return false;
        }

        return Url::to($config['base_url'] . '/' . $fileName, $scheme);
    }
}