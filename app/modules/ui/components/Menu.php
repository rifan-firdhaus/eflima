<?php namespace modules\ui\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property array $item
 * @property array $items
 */
class Menu extends BaseObject
{
    public $separator = '/';
    public $active;
    public $breadcrumbs = [];
    protected $_items = [];
    protected $_tree = [];
    protected $_count = 10000;

    /**
     * @param array $items
     */
    public function addItems($items)
    {
        foreach ($items AS $id => $options) {
            $this->addItem($id, $options);
        }
    }

    /**
     * @param       $id
     * @param array $options
     */
    public function addItem($id, $options = [])
    {
        if (!isset($options['sort'])) {
            $options['sort'] = $this->_count++;
        }

        $this->_items[$id] = isset($this->_items[$id]) ? ArrayHelper::merge($this->_items[$id], $options) : $options;
    }

    /**
     * @param $id
     */
    public function removeItem($id)
    {
        unset($this->_items[$id]);
    }

    /**
     * @param string $id
     * @param array  $options
     */
    public function setItem($id, $options = [])
    {
        $this->_items[$id] = isset($this->_items[$id]) ? ArrayHelper::merge($this->_items[$id], $options) : $this->addItem($id, $options);
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * @param null|string $active
     * @param null|string $from
     *
     * @return array
     */
    public function breadcrumbs($active = null, $from = null)
    {
        !is_null($active) || ($active = $this->active);
        $parent = '';
        $ids = explode($this->separator, $active);
        $isContinue = true;
        $breadcrumbs = [];

        foreach ($ids AS $id) {
            if (!empty($parent)) {
                $parent .= $this->separator;
            }
            $parent .= $id;

            if (!is_null($from) && $isContinue) {
                $id !== $from || ($isContinue = false);
                continue;
            }
            $breadcrumbs[] = $this->getItem($parent);
        }

        return array_filter(ArrayHelper::merge($breadcrumbs, $this->breadcrumbs));
    }

    /**
     * @param string $id
     *
     * @return array|bool|mixed
     */
    public function getItem($id)
    {
        return isset($this->_items[$id]) ? $this->_items[$id] : false;
    }

    /**
     * @param string $parent
     *
     * @return mixed
     */
    public function getTree($parent = '')
    {
        $menu = [
            'items' => [],
        ];
        $parent .= $this->separator;

        foreach ($this->_items AS $id => $options) {
            if ($parent != $this->separator) {
                if (strpos($id, $parent) === 0) {
                    $id = str_replace($parent, '', $id);
                } else {
                    continue;
                }
            }

            $this->generateTree($id, $parent, $options, $menu);
        }

        return self::sortItems($menu['items']);
    }

    /**
     * @param string $id
     * @param string $parent
     * @param array  $options
     * @param array  $tree
     */
    protected function generateTree($id, $parent, $options, &$tree)
    {
        if (strpos($id, $this->separator) !== false) {
            list($realParent, $realId) = explode($this->separator, $id, 2);

            if (!isset($tree['items'][$realParent])) {
                $tree['items'][$realParent] = [
                    'sort' => $this->_count++,
                    'items' => [],
                ];
            }

            $this->generateTree($realId, $parent, $options, $tree['items'][$realParent]);
        } else {
            $tree['items'][$id] = !isset($tree['items'][$id]) ? $options : ArrayHelper::merge($tree['items'][$id], $options);
        }
    }

    /**
     * @param array $items
     *
     * @return array
     */
    protected function sortItems(&$items = [])
    {
        $count = 0;

        ArrayHelper::multisort($items, function ($item) use (&$count) {
            $sort = $count;

            if (is_array($item) && isset($item['sort'])) {
                $sort = $item['sort'];
            } else {
                $count += 10;
            }

            return $sort;
        });

        foreach ($items AS &$item) {
            if (!empty($item['items'])) {
                self::sortItems($item['items']);
            }
        }

        return $items;
    }
}