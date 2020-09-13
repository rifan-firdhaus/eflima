<?php namespace modules\support\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\support\models\queries\TicketDepartmentQuery;
use modules\support\models\TicketDepartment;
use yii\helpers\Html;
use modules\ui\widgets\inputs\Select2Input;
use yii\base\InvalidConfigException;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property TicketDepartmentQuery $query
 */
class TicketDepartmentInput extends Select2Input
{
    public $_query;
    public $idAttribute = 'id';
    public $labelAttribute = 'name';
    public $jsOptions = [
        'width' => '100%',
    ];
    public $aliasAttribute;
    public $aliasName;
    public $aliasValue;
    public $aliasOptions;
    public $allowAdd = true;

    /**
     * @param TicketDepartmentQuery $query
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * @return TicketDepartmentQuery
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if (!isset($this->_query)) {
            $this->_query = TicketDepartment::find();
        }

        return $this->_query;
    }

    /**
     * @inheritdoc
     */
    public function normalize()
    {
        $this->source = $this->query->select([$this->idAttribute, $this->labelAttribute])->map($this->idAttribute, $this->labelAttribute);

        parent::normalize();

        if ($this->allowAdd) {
            if (!isset($this->aliasOptions['id'])) {
                $this->aliasOptions['id'] = $this->hasModel() && $this->aliasAttribute ? Html::getInputId($this->model,
                    Html::getAttributeName($this->aliasAttribute)) : ($this->options['id'] . '-alias');
            }

            $this->jsOptions['tags'] = true;
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $select2 = parent::run();

        if ($this->allowAdd) {
            if ($this->hasModel() && $this->aliasAttribute) {
                $alias = Html::activeHiddenInput($this->model, $this->aliasAttribute, $this->aliasOptions);
            } else {
                $alias = Html::hiddenInput($this->aliasName, $this->aliasValue, $this->aliasOptions);
            }
        }

        return $select2 . (isset($alias) ? $alias : '');
    }

    public function registerAssets()
    {
        if ($this->allowAdd) {
            $createTagJs = "function(params){return {id: params.term,text: params.term,isNew: true}}";
            $templateJs = 'function(data){return data.isNew === true ? $(\'<div><i class="icons8-plus icons8-size mr-2"></i>\'+data.text+\'</div>\') : data.text}';

            $this->jsOptions['templateResult'] = new JsExpression($templateJs);
            $this->jsOptions['createTag'] = new JsExpression($createTagJs);

            $createTagEventJs = "$('#{$this->options['id']}').on('select2:select',function(e){ $('#{$this->aliasOptions['id']}').val((e.params.data.isNew === true ? e.params.data.id : '')) })";

            $this->view->registerJs($createTagEventJs);
        }

        parent::registerAssets();
    }
}