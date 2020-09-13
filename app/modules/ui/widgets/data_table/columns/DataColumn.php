<?php namespace modules\ui\widgets\data_table\columns;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use ArrayAccess;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQueryInterface;
use yii\helpers\ArrayHelper;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class DataColumn extends Column
{
    /**
     * @inheritdoc
     */
    protected function renderContent($model, $id, $index)
    {
        return $this->getColumnValue($model, $id, $index);
    }

    /**
     * @param array|ArrayAccess $model
     * @param mixed             $id
     * @param mixed             $index
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public function getColumnValue($model, $id, $index)
    {
        if (empty($this->content)) {
            $this->content = $this->attribute;
        }

        if (is_string($this->content)) {
            return ArrayHelper::getValue($model, $this->content);
        } elseif (is_callable($this->content)) {
            return call_user_func($this->content, $model, $id, $index);
        }

        throw new InvalidConfigException("Either content or attribute config must be set");
    }

    public function normalize()
    {
        if (!isset($this->label)) {
            $this->label = $this->guessLabel();
        }

        parent::normalize();
    }

    public function guessLabel()
    {
        $provider = $this->dataTable->dataProvider;

        if ($provider instanceof ActiveDataProvider && $provider->query instanceof ActiveQueryInterface) {
            /* @var $modelClass Model */
            $modelClass = $provider->query->modelClass;
            $model = $modelClass::instance();

            return $model->getAttributeLabel($this->attribute);
        } elseif ($provider instanceof ArrayDataProvider && $provider->modelClass !== null) {
            /* @var $modelClass Model */
            $modelClass = $provider->modelClass;
            $model = $modelClass::instance();

            return $model->getAttributeLabel($this->attribute);
        } elseif ($this->dataTable->searchModel !== null && $this->dataTable->searchModel instanceof Model) {
            return $this->dataTable->searchModel->getAttributeLabel($this->attribute);
        } else {
            $models = $provider->getModels();

            if (($model = reset($models)) instanceof Model) {
                /* @var $model Model */
                return $model->getAttributeLabel($this->attribute);
            }
        }

        return null;
    }
}