<?php namespace modules\ui\widgets\inputs;

use yii\base\Component;
use yii\debug\models\timeline\DataProvider;
use yii\helpers\ArrayHelper;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Select2Data extends Component
{
    public $id;
    public $label;
    public $attributes = [];

    /** @var DataProvider */
    public $dataProvider;

    public function serialize()
    {
        $result = [];

        $attributes = $this->attributes;
        $attributes['id'] = $this->id;
        $attributes['text'] = $this->label;

        foreach ($this->dataProvider->models AS $key => $model) {
            $result[$key] = self::serializeModel($model,$attributes);
        }

        return [
            'total_count' => $this->dataProvider->totalCount,
            'results' => $result,
            'pagination' => [
                'more' => $this->dataProvider->pagination->pageCount > 0 && $this->dataProvider->pagination->page != $this->dataProvider->pagination->pageCount - 1,
            ],
        ];
    }

    public static function serializeModel($model,$attributes){
        $result = [];

        foreach ($attributes AS $attributeKey => $attribute) {
            if (is_numeric($attributeKey) && is_string($attribute)) {
                $result[$attribute] = ArrayHelper::getValue($model, $attribute);
            } elseif (is_callable($attribute)) {
                $result[$attributeKey] = call_user_func($attribute, $model);
            } else {
                $result[$attributeKey] = ArrayHelper::getValue($model, $attribute);
            }
        }

        return $result;
    }
}