<?php namespace modules\crm\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\crm\models\CustomerGroup;
use modules\crm\models\queries\CustomerGroupQuery;
use yii\helpers\Html;
use modules\ui\widgets\inputs\Select2Input;
use yii\base\InvalidConfigException;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property CustomerGroupQuery $query
 */
class CustomerGroupInput extends Select2Input
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
     * @param CustomerGroupQuery $query
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * @return CustomerGroupQuery
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if (!isset($this->_query)) {
            $this->_query = CustomerGroup::find();
        }

        return $this->_query;
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

    /**
     * @inheritdoc
     */
    public function normalize()
    {
        $models = $this->query->select(['id', 'name', 'color_label'])->createCommand()->queryAll();

        foreach ($models AS $model) {
            $id = $model[$this->idAttribute];
            $this->source[$id] = $model[$this->labelAttribute];
            $this->options['options'][$id] = ['data-color' => $model['color_label']];
        }

        $this->jsOptions['templateResult'] = new JsExpression(
        /** @lang JavaScript */
            "function(data){
                if(data){
                    if(data.isNew){
                      return $('<div><i class=\"icons8-plus icons8-size mr-2\"></i>'+data.text+'</div>');
                    }
                
                    var _state = $('<span class=\"color-description\"></span><span>'+data.text+'</span>');
                    var color=$(data.element).data('color');
                    
                    if(color){
                        _state.filter('.color-description').css('background-color',color)
                    }else{
                        _state.filter('.color-description').addClass('d-none')
                    }
                    
                    return _state
                }
                return '';
            }"
        );
        $this->jsOptions['templateSelection'] = $this->jsOptions['templateResult'];

        parent::normalize();

        if ($this->allowAdd) {
            if (!isset($this->aliasOptions['id'])) {
                $this->aliasOptions['id'] = $this->hasModel() && $this->aliasAttribute ? Html::getInputId($this->model,
                    Html::getAttributeName($this->aliasAttribute)) : ($this->options['id'] . '-alias');
            }

            $this->jsOptions['tags'] = true;

            $createTagJs = "function(params){return {id: params.term,text: params.term,isNew: true}}";
            $this->jsOptions['createTag'] = new JsExpression($createTagJs);
        }
    }

    public function registerAssets()
    {
        parent::registerAssets();

        if ($this->allowAdd) {
            $createTagEventJs = "$('#{$this->options['id']}').on('select2:select',function(e){ $('#{$this->aliasOptions['id']}').val((e.params.data.isNew === true ? e.params.data.id : '')) })";

            $this->view->registerJs($createTagEventJs);
        }
    }
}