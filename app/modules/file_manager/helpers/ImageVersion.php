<?php namespace modules\file_manager\helpers;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Closure;
use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\StaticInstanceInterface;
use yii\base\StaticInstanceTrait;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\imagine\Image;
use function basename;
use function call_user_func;
use function file_exists;
use function is_callable;
use function is_dir;
use function is_file;
use function ltrim;
use function pathinfo;
use function str_replace;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class ImageVersion implements StaticInstanceInterface
{
    use StaticInstanceTrait;

    protected $_versions = [];

    /**
     * @param string      $id
     * @param string      $image
     * @param bool|string $scheme
     *
     * @return string
     * @throws Exception
     */
    public function url($id, $image, $scheme = true)
    {
        $path = $this->path($id, $image);
        $root = FileHelper::normalizePath(Yii::getAlias('@webroot'));
        $path = Yii::getAlias('@web') . '/' . ltrim(str_replace($root, '', $path), "\\/");

        return Url::to(FileHelper::normalizePath($path, '/'), $scheme);
    }

    /**
     * @param string $id
     * @param string $image
     *
     * @return string
     * @throws Exception
     */
    public function path($id, $image)
    {
        if (!$this->generate($id, $image)) {
            return false;
        }

        return $this->derivativePath($id, $image);
    }

    /**
     * @param string $id
     * @param string $image
     *
     * @return bool
     *
     * @throws Exception
     */
    public function generate($id, $image)
    {
        if (!isset($this->_versions[$id])) {
            throw new InvalidArgumentException("ImageVersion with id:{$id} doesn't exists");
        }

        $image = FileHelper::normalizePath($image);

        if (!file_exists($image) || !is_file($image)) {
            throw new InvalidArgumentException("Image: {$image} doesn't exists");
        }

        $newImage = $this->derivativePath($id, $image);

        if (file_exists($newImage) && is_file($newImage)) {
            return $newImage;
        }

        return call_user_func($this->_versions[$id], $image, $newImage);
    }

    /**
     * @param string $id
     * @param string $image
     *
     * @return bool|string
     *
     * @throws Exception
     */
    protected function derivativePath($id, $image)
    {
        $originalDirectory = FileHelper::normalizePath(pathinfo($image, PATHINFO_DIRNAME));
        $originalBaseName = basename($image);
        $derivativeDirectory = $originalDirectory . DIRECTORY_SEPARATOR . '_.' . $id;

        if (!is_dir($derivativeDirectory) && !FileHelper::createDirectory($derivativeDirectory)) {
            throw new InvalidArgumentException("Failed to create directory: {$derivativeDirectory}");
        }

        return $derivativeDirectory . DIRECTORY_SEPARATOR . $originalBaseName;
    }

    /**
     * @param string           $id
     * @param callable|Closure $generator
     *
     * @return $this
     */
    public function register($id, $generator)
    {
        if (!is_callable($generator)) {
            throw new InvalidArgumentException('Generator must be a callable');
        }

        $this->_versions[$id] = $generator;

        return $this;
    }

    /**
     * @return array
     */
    public function getVersions()
    {
        return array_keys($this->_versions);
    }

    public function placeholder($text)
    {
        $backgrounds = [
            '#F44336',
            '#E91E63',
            '#9C27B0',
            '#673AB7',
            '#3F51B5',
            '#2196F3',
            '#03A9F4',
            '#009688',
            '#4CAF50',
            '#8BC34A',
            '#CDDC39',
            '#FFC107',
            '#FF9800',
            '#FF5722',
            '#795548',
            '#9E9E9E',
            '#607D8B',
            '#B71C1C',
            '#880E4F',
            '#4A148C',
            '#311B92',
            '#1A237E',
            '#0D47A1',
            '#01579B',
            '#006064',
            '#004D40',
            '#1B5E20',
            '#33691E',
            '#827717',
            '#F57F17',
            '#FF6F00',
            '#E65100',
            '#BF360C',
            '#3E2723',
            '#212121',
            '#263238',
        ];

        $words = array_filter(explode(' ', $text));
        $initial = '';

        foreach ($words AS $key => $word) {
            $initial .= strtoupper($word[0]);

            if ($key == 1) {
                break;
            }
        }

        $background = $backgrounds[array_rand($backgrounds)];

        $pallete = new RGB();
        $imageBox = new Box(300, 300);

        $image = Image::getImagine()->create($imageBox, $pallete->color($background));

        $fontFamily = Yii::getAlias('@modules/account/assets/source/fonts/Montserrat-Bold.ttf');
        $fontColor = $pallete->color('#FFF');
        $font = Image::getImagine()->font($fontFamily, 90, $fontColor);
        $fontBox = $font->box($initial, 0);
        $fontPointX = ceil($imageBox->getWidth() / 2) - ceil($fontBox->getWidth() / 2);
        $fontPointY = ceil($imageBox->getHeight() / 2) - ceil($fontBox->getHeight() / 2);
        $fontPoint = new Point($fontPointX, $fontPointY);

        $image->draw()->text($initial, $font, $fontPoint, 0);

        return $image;
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public static function isImage($file){
        return getimagesize($file) !== false;
    }
}