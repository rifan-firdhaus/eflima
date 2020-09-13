<?php namespace modules\ui\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\core\components\BaseSettingObject;
use yii\helpers\Html;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\fields\RawField;
use Yii;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 */
class SettingObject extends BaseSettingObject
{
    /**
     * @inheritdoc
     */
    public function render()
    {
        switch ($this->renderer->section) {
            case 'pusher':
                $this->renderPusherSection();
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        switch ($this->renderer->section) {
            case 'pusher':
                $this->initPusherSection();
                break;
        }
    }

    public function initPusherSection()
    {
        $this->renderer->addFields([
            'pusher/is_enabled' => [
                'label' => Yii::t('app', 'Enabled'),
                'rules' => [
                    'boolean',
                ],
            ],
            'pusher/app_id' => [
                'label' => Yii::t('app', 'APP ID'),
                'rules' => [
                    'string',
                ],
            ],
            'pusher/app_key' => [
                'label' => Yii::t('app', 'APP Key'),
                'rules' => [
                    'string',
                ],
            ],
            'pusher/app_secret' => [
                'label' => Yii::t('app', 'APP Secret'),
                'rules' => [
                    'string',
                ],
            ],
            'pusher/cluster' => [
                'label' => Yii::t('app', 'Cluster'),
                'rules' => [
                    'string',
                ],
            ],
        ]);

        $this->renderer->addSubSection('pusher', [
            'label' => Yii::t('app', 'Pusher'),
            'card' => [
                'icon' => 'i8:shield',
            ],
        ]);
    }

    public function renderPusherSection()
    {
        return $this->renderer->getSubSection('pusher')
            ->addFields($this->getPusherFields());
    }

    public function getPusherFields()
    {
        return [
            [
                'class' => RawField::class,
                'inputOnly' => true,
                'input' => Html::tag('div',Yii::t('app','Pusher enables system to make realtime update, to get APP ID, APP Key, App Secret and Cluster, you need to sign up to {link}, after login to your pusher account, go to <code>[Channel Apps > Select Your App > Click App Keys]</code> to get all those information ',[
                    'link' => Html::a('pusher.com','https://pusher.com')
                ]),[
                    'class' => 'mb-3'
                ]),
            ],
            [
                'class' => ContainerField::class,
                'inputOnly' => true,
                'fields' => [
                    [
                        'size' => 'col-6',
                        'field' => [
                            'attribute' => 'value',
                            'model' => $this->renderer->getModel('pusher/is_enabled'),
                            'type' => ActiveField::TYPE_RADIO_LIST,
                            'source' => [
                                '0' => Yii::t('app', 'Disable'),
                                '1' => Yii::t('app', 'Enable'),
                            ],
                            'options' => [
                                'class' => 'form-group d-flex align-items-center',
                            ],
                            'inputOptions' => [
                                'itemOptions' => [
                                    'custom' => true,
                                    'inline' => true,
                                ],
                            ],
                        ],
                    ],
                    [
                        'size' => 'col-6',
                        'field' => [
                            'class' => RawField::class,
                        ],
                    ],
                    [
                        'size' => 'col-6',
                        'field' => [
                            'attribute' => 'value',
                            'model' => $this->renderer->getModel('pusher/app_id'),
                        ],
                    ],
                    [
                        'size' => 'col-6',
                        'field' => [
                            'attribute' => 'value',
                            'model' => $this->renderer->getModel('pusher/app_key'),
                        ],
                    ],
                    [
                        'size' => 'col-6',
                        'field' => [
                            'attribute' => 'value',
                            'model' => $this->renderer->getModel('pusher/app_secret'),
                        ],
                    ],
                    [
                        'size' => 'col-6',
                        'field' => [
                            'attribute' => 'value',
                            'model' => $this->renderer->getModel('pusher/cluster'),
                        ],
                    ],
                ],
            ],
        ];
    }
}