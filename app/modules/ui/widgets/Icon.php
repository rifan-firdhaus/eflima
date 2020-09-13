<?php namespace modules\ui\widgets;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\helpers\Html;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\web\View;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class Icon extends BaseObject
{
    protected static $registeredIcons = [];

    /**
     * @param       $id
     * @param array $options Options of icon
     *                       - tag
     *                       - prefixClass
     *                       - suffixClass
     *                       - asset
     *                       - class
     *                       - iconLigature
     *                       - options
     *
     */
    public static function register($id, $options = [])
    {
        $defultOptions = [
            'tag' => 'div',
            'prefixClass' => '',
            'suffixClass' => '',
            'class' => '',
            'iconLigature' => false,
            'options' => [],
        ];

        self::$registeredIcons[$id] = ArrayHelper::merge($defultOptions, $options);
    }

    /**
     * @param string|array $icon
     * @param array        $options
     * @param View         $view
     *
     * @return string
     */
    public static function show($icon, $options = [], $view = null)
    {
        if (strpos($icon, ':') === false) {
            throw new InvalidArgumentException("ID of icon undefined");
        }

        list($id, $iconName) = explode(':', $icon, 2);

        $iconOptions = self::getIconOptions($id);
        $text = '';

        $options = ArrayHelper::merge($iconOptions['options'], $options);

        if (!$iconOptions['iconLigature']) {
            $class = $iconOptions['prefixClass'] . $iconName . $iconOptions['suffixClass'];

            Html::addCssClass($options, $class);
        } else {
            $text = $icon;
        }

        if ($iconOptions['asset']) {
            if ($view === null) {
                $view = Yii::$app->view;
            }

            $iconOptions['asset']::register($view);
        }

        return Html::tag($iconOptions['tag'], $text, $options);
    }

    /**
     * @param $id
     *
     * @return array
     */
    public static function getIconOptions($id)
    {
        if (!isset(self::$registeredIcons[$id])) {
            throw new InvalidArgumentException("Icon with id: {$id} doesn't exists");
        }

        return self::$registeredIcons[$id];
    }
}