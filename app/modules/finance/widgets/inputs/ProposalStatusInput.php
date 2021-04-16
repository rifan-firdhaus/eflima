<?php namespace modules\finance\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\finance\models\queries\ProposalStatusQuery;
use modules\finance\models\ProposalStatus;
use modules\ui\widgets\inputs\Select2Input;
use yii\base\InvalidConfigException;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property ProposalStatusQuery $query
 */
class ProposalStatusInput extends Select2Input
{
    public $_query;
    public $idAttribute = 'id';
    public $labelAttribute = 'label';
    public $jsOptions = [
        'minimumResultsForSearch' => -1,
        'width' => '100%',
    ];

    /**
     * @param ProposalStatusQuery $query
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * @return ProposalStatusQuery
     * @throws InvalidConfigException
     */
    public function getQuery()
    {
        if (!isset($this->_query)) {
            $this->_query = ProposalStatus::find();
        }

        return $this->_query;
    }

    /**
     * @inheritdoc
     */
    public function normalize()
    {
        $models = $this->query->select(['id', 'label', 'color_label'])->createCommand()->queryAll();

        foreach ($models AS $model) {
            $id = $model[$this->idAttribute];
            $this->source[$id] = $model[$this->labelAttribute];
            $this->options['options'][$id] = ['data-color' => $model['color_label']];
        }

        $this->jsOptions['templateResult'] = new JsExpression(
        /** @lang JavaScript */
            "function(data){
                if(data){
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
    }
}
