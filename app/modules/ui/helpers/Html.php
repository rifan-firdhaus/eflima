<?php namespace yii\helpers;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\ui\widgets\Icon;
use Yii;
use function str_replace;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Html extends BaseHtml
{
    public static function cssSelector($options)
    {
        $selector = ArrayHelper::getValue($options, 'tag', '');

        if (isset($options['id'])) {
            return $selector . '#' . $options['id'];
        }

        if (isset($options['class'])) {
            $selector = $selector . '.' . implode('.', preg_split('/\s+/', $options['class'], -1, PREG_SPLIT_NO_EMPTY));
        }

        if (isset($options['name'])) {
            $selector = $selector . "[name='{$options['name']}']";
        }

        return $selector;
    }

    /**
     * @inheritDoc
     *
     * @param string|array      $text
     * @param null|array|string $url
     * @param array             $options
     *
     * @return string
     */
    public static function a($text, $url = null, $options = [])
    {
        if (is_array($text)) {
            return self::a(ArrayHelper::remove($text, 'label', ''), ArrayHelper::remove($text, 'url', null), $text);
        }

        $icon = ArrayHelper::remove($options, 'icon');

        if ($icon) {
            $text = Icon::show($icon) . $text;
        }

        return parent::a($text, $url, $options);
    }

    /**
     * @param      $color
     * @param bool $opacity
     *
     * @return string
     * @author https://mekshq.com/how-to-convert-hexadecimal-color-code-to-rgb-or-rgba-using-php/
     */
    public static function hex2rgba($color, $opacity = false)
    {
        $default = 'rgb(0,0,0)';

        //Return default if no color provided
        if (empty($color)) {
            return $default;
        }

        //Sanitize $color if "#" is provided
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
            $hex = [$color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]];
        } elseif (strlen($color) == 3) {
            $hex = [$color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]];
        } else {
            return $default;
        }

        //Convert hexadec to rgb
        $rgb = array_map('hexdec', $hex);

        //Check if opacity is set(rgba or rgb)
        if ($opacity) {
            if (abs($opacity) > 1) {
                $opacity = 1.0;
            }
            $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
        } else {
            $output = 'rgb(' . implode(",", $rgb) . ')';
        }

        //Return rgb(a) color string
        return $output;
    }

    public static function getInputId($model, $attribute)
    {
        return self::getRealInputId($model, $attribute) . '-' . Yii::$app->view->uniqueId;
    }

    public static function getRealInputId($model, $attribute)
    {
        $inputId = parent::getInputId($model, $attribute);

        return str_replace("/", '-', $inputId);
    }

    protected static function booleanInput($type, $name, $checked = false, $options = [])
    {
        $custom = ArrayHelper::remove($options, 'custom', false);

        if ($custom) {
            $labelText = ArrayHelper::remove($options, 'label', false);
            $labelOptions = ArrayHelper::remove($options, 'labelOptions', false);
            $containerOptions = ArrayHelper::remove($options, 'containerOptions', false);
            $inline = ArrayHelper::remove($options, 'inline', false);

            if (!isset($options['id'])) {
                $options['id'] = uniqid();
            }

            Html::addCssClass($options, 'custom-control-input');
        }

        $checkbox = parent::booleanInput($type, $name, $checked, $options);

        if ($custom) {
            Html::addCssClass($labelOptions, 'custom-control-label');
            Html::addCssClass($containerOptions, "custom-control custom-{$type}");

            if ($inline) {
                Html::addCssClass($containerOptions, "custom-control-inline");
            }

            $label = Html::label($labelText, $options['id'], $labelOptions);

            if (empty($labelText)) {
                Html::addCssClass($containerOptions, 'no-label');
            }

            return Html::tag('div', $checkbox . $label, $containerOptions);
        }

        return $checkbox;
    }

    public static function activeInput($type, $model, $attribute, $options = [])
    {
        if (!array_key_exists('id', $options)) {
            $options['id'] = static::getInputId($model, $attribute);
            $options['data-rid'] = static::getRealInputId($model, $attribute);
        }

        return parent::activeInput($type, $model, $attribute, $options);
    }
}