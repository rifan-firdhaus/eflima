<?php namespace modules\finance\models;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

use modules\core\db\ActiveQuery;
use modules\core\db\ActiveRecord;
use modules\file_manager\behaviors\FileUploaderBehavior;
use modules\finance\models\queries\ExpenseAttachmentQuery;
use modules\finance\models\queries\ExpenseQuery;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property Expense $expense
 *
 * @property int     $id         [int(10) unsigned]
 * @property int     $expense_id [int(11) unsigned]
 * @property string  $file
 * @property int     $uploaded_at [int(11) unsigned]
 */
class ExpenseAttachment extends ActiveRecord
{
    public $uploaded_file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%expense_attachment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uploaded_file'], 'file'],
        ];
    }
    /**
     * @inheritdoc
     *
     * @return ExpenseAttachmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new ExpenseAttachmentQuery(get_called_class());

        return $query->alias("expense_attachment");
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['fileUploader'] = [
            'class' => FileUploaderBehavior::class,
            'attributes' => [
                'file' => [
                    'alias' => 'uploaded_file',
                    'base_path' => '@webroot/protected/system/expense/attachment',
                    'base_url' => '@web/protected/system/expense/attachment',
                ],
            ]
        ];

        return $behaviors;
    }

    /**
     * @return ActiveQuery|ExpenseQuery
     */
    public function getExpense()
    {
        return $this->hasOne(Expense::class, ['id' => 'expense_id'])->alias('expense_of_attachment');
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'expense_id' => Yii::t('app', 'Expense'),
            'file' => Yii::t('app', 'File'),
        ];
    }
}
