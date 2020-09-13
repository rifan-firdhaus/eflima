<?php namespace modules\ui\widgets\inputs;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use Yii;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class MultipleEmailInput extends Select2Input
{
    public function normalize()
    {
        $invalidEmailMessage = Json::encode(Yii::t('yii','{attribute} is not a valid email address.'));
        $emailPattern = "/^[a-zA-Z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/";
        $this->multiple = true;
        $this->jsOptions['tags'] = true;
        $this->jsOptions['tokenSeparators'] = [',', ' '];
        $this->jsOptions['createTag'] = new JsExpression("
            function(params){
              var messages = [];
              var term = params.term;
              
              yii.validation.email(term,messages,{
                message: {$invalidEmailMessage},
                pattern: {$emailPattern}
              });
              
              if(messages.length > 0){
                return null;
              }
              
              return {
                  id: params.term,
                  text: params.term
              };
            }
        ");

        parent::normalize();
    }
}