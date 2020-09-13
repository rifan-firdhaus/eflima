<?php namespace modules\file_manager\web;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\web\UploadedFile as BaseUploadedFile;

/**
 * Description of UploadFile
 *
 * @author Rifan Firdhaus <rifanfirdhaus@gmail.com>
 */
class UploadedFile extends BaseUploadedFile
{
    public $overwrite = false;
    public $slugify = true;

    /**
     * @inheritdoc
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        if ($this->slugify) {
            $directory = pathinfo($file, PATHINFO_DIRNAME);
            $filename = Inflector::slug(pathinfo($file, PATHINFO_FILENAME));
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $file = $directory . '/' . $filename . '.' . $extension;
        }

        $file = Yii::getAlias($file);
        $this->overwrite || ($file = $this->getUniqueName($file));

        $directory = pathinfo($file, PATHINFO_DIRNAME);

        // Create directory if it doesn't exists
        if (!is_dir($directory) && !FileHelper::createDirectory($directory)) {
            return false;
        }

        if (parent::saveAs($file, $deleteTempFile)) {
            return $file;
        }

        return false;
    }

    /**
     * @param     $file
     * @param int $_counter
     *
     * @return string
     */
    protected function getUniqueName($file, $_counter = 0)
    {
        if ($_counter > 0) {
            $directory = pathinfo($file, PATHINFO_DIRNAME);
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $_file = $directory . '/' . $filename . '-' . $_counter . '.' . $extension;
        } else {
            $_file = $file;
        }

        if (file_exists($_file)) {
            return $this->getUniqueName($file, $_counter + 1);
        }

        return $_file;
    }

}