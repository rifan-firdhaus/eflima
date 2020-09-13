<?php namespace modules\core\web;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\AssetBundle as BaseAssetBundle;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class AssetBundle extends BaseAssetBundle
{
    /**
     * @inheritdoc
     */
    public function publish($am)
    {
        if (YII_ENV_DEV) {
            $path = FileHelper::normalizePath(Yii::getAlias($this->sourcePath), '/');
            $rootPath = Yii::getAlias('@webroot');
            $baseUrl = Url::to(Yii::getAlias('@web') . substr($path, strlen($rootPath)), true);

            $this->basePath = $this->sourcePath;
            $this->baseUrl = $baseUrl;
        }


        parent::publish($am);
    }
}