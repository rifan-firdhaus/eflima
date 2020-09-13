<?php namespace modules\finance\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\Invoice;
use modules\finance\widgets\inputs\InvoiceInput;
use modules\task\components\TaskRelation;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property mixed    $label
 * @property Invoice $model
 */
class InvoiceTaskRelation extends TaskRelation
{
    use InvoiceRelatedTrait;

    public $useDefaultPicker = false;

    /**
     * @inheritDoc
     */
    public function pickerInput($task, $attribute)
    {
        return InvoiceInput::widget([
            'model' => $task,
            'attribute' => $attribute,
        ]);
    }
}