<?php namespace modules\core\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\db\ActiveRecord;
use modules\file_manager\behaviors\FileUploaderBehavior;
use modules\file_manager\web\UploadedFile;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property string $id          [varchar(64)]
 * @property string $value
 * @property bool   $is_autoload [tinyint(1)]
 * @property bool   $is_file     [tinyint(1)]
 * @property int    $updated_at  [int(11) unsigned]
 */
class Setting extends ActiveRecord
{
    /** @var UploadedFile */
    public $uploaded_value;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "{{%setting}}";
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return parent::find()->alias("setting");
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
            'createdAtAttribute' => false,
        ];

        $behaviors['fileUploader'] = [
            'class' => FileUploaderBehavior::class,
            'attributes' => [
                'value' => [
                    'alias' => 'uploaded_value',
                    'base_path' => '@webroot/protected/system/setting',
                    'base_url' => '@web/protected/system/setting',
                    'is_file' => function ($model) {
                        /** @var Setting $model */

                        return $model->is_file;
                    },
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'value' => Yii::t('app', 'Value'),
            'is_autoload' => Yii::t('app', 'Is Autoload'),
            'is_file' => Yii::t('app', 'Is File'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}