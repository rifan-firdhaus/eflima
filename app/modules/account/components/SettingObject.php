<?php namespace modules\account\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use modules\address\widgets\inputs\CountryInput;
use modules\core\components\BaseSettingObject;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\ui\widgets\form\fields\ActiveField;
use modules\ui\widgets\form\fields\InputField;
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
            case 'account':
                $this->renderAccountSection();
                break;
            case 'company':
                $this->renderCompanySection();
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        switch ($this->renderer->section) {
            case 'account':
                $this->initAccountSection();
                break;
            case 'company':
                $this->initCompanySection();
                break;
        }
    }

    protected function getCompanyFields()
    {
        return [
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('company/name'),
            ],
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('company/country_code'),
                'type' => ActiveField::TYPE_WIDGET,
                'widget' => [
                    'class' => CountryInput::class,
                ],
            ],
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('company/province'),
            ],
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('company/city'),
            ],
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('company/address'),
                'type' => ActiveField::TYPE_TEXTAREA,
            ],
            [
                'model' => $this->renderer->getModel('logo'),
                'attribute' => 'uploaded_value',
                'type' => InputField::TYPE_WIDGET,
                'widget' => [
                    'class' => FileUploaderInput::class,
                ],
            ],
        ];
    }

    protected function initCompanySection()
    {
        $this->renderer->addFields([
            'company/name' => [
                'label' => Yii::t('app', 'Company Name'),
                'rules' => [
                    'required',
                ],
            ],
            'company/address' => [
                'label' => Yii::t('app', 'Address'),
                'rules' => [
                    'required',
                ],
            ],
            'company/country_code' => [
                'label' => Yii::t('app', 'Country'),
                'rules' => [
                    'required',
                ],
            ],
            'company/province' => [
                'label' => Yii::t('app', 'Province'),
                'rules' => [
                    'required',
                ],
            ],
            'company/city' => [
                'label' => Yii::t('app', 'City'),
                'rules' => [
                    'required',
                ],
            ],
            'company/postal_code' => [
                'label' => Yii::t('app', 'Postal Code'),
                'rules' => [
                    'safe',
                ],
            ],
            'company/phone' => [
                'label' => Yii::t('app', 'Phone'),
                'rules' => [
                    'safe',
                ],
            ],
            'company/vat_number' => [
                'label' => Yii::t('app', 'VAT Number'),
                'rules' => [
                    'safe',
                ],
            ],
            'logo' => [
                'label' => Yii::t('app', 'Logo'),
                'rules' => [
                    ['image', 'maxSize' => 8 * 1024 * 1024],
                ],
            ],
        ]);

        $this->renderer->addSubSection('company', [
            'label' => Yii::t('app', 'Company'),
            'card' => [
                'icon' => 'i8:smart-card',
            ],
        ]);
    }

    protected function renderCompanySection()
    {
        $this->renderer->getSubSection('company')
            ->addFields($this->getCompanyFields());
    }

    protected function getPrivacyFields()
    {
        return [
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('is_session_log_enabled'),
                'standalone' => true,
                'type' => InputField::TYPE_CHECKBOX,
                'inputOptions' => [
                    'label' => Yii::t('app',
                        'Record Session (If it is enabled, system will record user information like IP and Browser they use, user with authority can see those information in their profile page)'),
                    'custom' => true,
                ],
            ],
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('session_log_size'),
                'type' => 'number',
                'hint' => Yii::t('app', 'System will delete older log automatically when size of log recorded in database reach this number'),
                'labelOptions' => [
                    'class' => 'pl-5',
                ],
                'options' => [
                    'class' => 'form-group mb-4',
                ],
            ],
            [
                'attribute' => 'value',
                'standalone' => true,
                'model' => $this->renderer->getModel('is_history_log_enabled'),
                'type' => InputField::TYPE_CHECKBOX,
                'inputOptions' => [
                    'label' => Yii::t('app',
                        'Record Activity (If it is enabled, system will record every activity the user does in their login session, user with authority can see those information in their profile page)'),
                    'custom' => true,
                ],
            ],
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('history_log_size'),
                'hint' => Yii::t('app', 'System will delete older log automatically when size of log recorded in database reach this number'),
                'type' => 'number',
                'labelOptions' => [
                    'class' => 'pl-5',
                ],
            ],
        ];
    }

    protected function initAccountSection()
    {
        $this->renderer->addFields([
            'is_session_log_enabled' => [
                'label' => Yii::t('app', 'Record Session'),
                'rules' => [
                    'boolean',
                ],
            ],
            'session_log_size' => [
                'label' => Yii::t('app', 'Session Record Size'),
                'rules' => [
                    ['double', 'min' => 0],
                ],
            ],
            'is_history_log_enabled' => [
                'label' => Yii::t('app', 'Record Activity'),
                'rules' => [
                    'boolean',
                ],
            ],
            'history_log_size' => [
                'label' => Yii::t('app', 'Activity Record Size'),
                'rules' => [
                    ['double', 'min' => 0],
                ],
            ],
        ]);

        $this->renderer->addSubSection('privacy', [
            'label' => Yii::t('app', 'Privacy'),
            'card' => [
                'icon' => 'i8:shield',
            ],
        ]);
    }

    protected function renderAccountSection()
    {
        $this->renderer->getSubSection('privacy')
            ->addFields($this->getPrivacyFields());
    }
}