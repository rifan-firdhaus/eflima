<?php namespace modules\core\components;

// "Keep the essence of your code, code isn't just a code, it's an art." -- Rifan Firdhaus Widigdo
use DateTime;
use DateTimeZone;
use Exception;
use modules\file_manager\widgets\inputs\FileUploaderInput;
use modules\ui\widgets\form\fields\ContainerField;
use modules\ui\widgets\form\fields\InputField;
use modules\ui\widgets\inputs\Select2Input;
use Yii;
use yii\base\InvalidConfigException;

/**
 * @author Rifan Firdhaus Widigdo <rifanfirdhaus@gmail.com>
 *
 * @property array $websiteFields
 * @property array $internationalitazionFields
 */
class SettingObject extends BaseSettingObject
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        switch ($this->renderer->section) {
            case 'general':
                $this->initGeneralSection();
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        switch ($this->renderer->section) {
            case 'general':
                $this->renderGeneralSection();
                break;
        }
    }

    /**
     * @return void
     * @throws InvalidConfigException
     */
    protected function renderGeneralSection()
    {
        $this->renderer->getSubSection('website')
            ->addFields($this->getWebsiteFields());

        $this->renderer->getSubSection('internationalization')
            ->addFields($this->getInternationalitazionFields());
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    protected function getWebsiteFields()
    {
        return [
            [
                'model' => $this->renderer->getModel('website_name'),
                'attribute' => 'value',
            ],
            [
                'model' => $this->renderer->getModel('website_description'),
                'type' => InputField::TYPE_TEXTAREA,
                'attribute' => 'value',
            ],
            [
                'model' => $this->renderer->getModel('meta_description'),
                'type' => InputField::TYPE_TEXTAREA,
                'attribute' => 'value',
            ],
            [
                'model' => $this->renderer->getModel('meta_keyword'),
                'type' => InputField::TYPE_TEXTAREA,
                'attribute' => 'value',
            ],
            [
                'model' => $this->renderer->getModel('logo'),
                'attribute' => 'uploaded_value',
                'type' => InputField::TYPE_WIDGET,
                'widget' => [
                    'class' => FileUploaderInput::class,
                ],
            ],
            [
                'model' => $this->renderer->getModel('favicon'),
                'attribute' => 'uploaded_value',
                'type' => InputField::TYPE_WIDGET,
                'widget' => [
                    'class' => FileUploaderInput::class,
                ],
            ],
        ];
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    protected function getInternationalitazionFields()
    {
        return [
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('number_format'),
                'type' => InputField::TYPE_DROP_DOWN_LIST,
                'source' => self::numberFormatList(),
            ],
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('timezone'),
                'type' => InputField::TYPE_WIDGET,
                'widget' => [
                    'source' => self::timezoneList(),
                    'class' => Select2Input::class,
                ],
            ],
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('datetime_display_format'),
                'type' => InputField::TYPE_DROP_DOWN_LIST,
                'source' => self::datetimeFormatList(),
            ],
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('date_display_format'),
                'type' => InputField::TYPE_DROP_DOWN_LIST,
                'source' => self::dateFormatList(),
            ],
            [
                'attribute' => 'value',
                'model' => $this->renderer->getModel('time_display_format'),
                'type' => InputField::TYPE_DROP_DOWN_LIST,
                'source' => self::timeFormatList(),
            ],
            [
                'class' => ContainerField::class,
                'label' => Yii::t('app', 'Date & Time Input Format'),
                'fields' => [
                    [
                        'size' => 'col-md-8',
                        'field' => [
                            'standalone' => true,
                            'model' => $this->renderer->getModel('date_input_format'),
                            'attribute' => 'value',
                            'type' => InputField::TYPE_DROP_DOWN_LIST,
                            'source' => self::dateInputFormatList(),
                        ],
                    ],
                    [
                        'size' => 'col-md-4',
                        'field' => [
                            'standalone' => true,
                            'model' => $this->renderer->getModel('time_input_format'),
                            'type' => InputField::TYPE_DROP_DOWN_LIST,
                            'source' => self::timeInputFormatList(),
                            'attribute' => 'value',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function numberFormatList()
    {
        $number = 1234567.89;

        return [
            'period' => (new Formatter(['decimalSeparator' => ',', 'thousandSeparator' => '.']))->asDecimal($number, 2),
            'comma' => (new Formatter(['decimalSeparator' => '.', 'thousandSeparator' => ',']))->asDecimal($number, 2),
        ];
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    public static function timezoneList()
    {
        $timezoneList = Yii::$app->cache->get('timezoneList');

        if ($timezoneList === false) {
            $timezoneIdentifiers = DateTimeZone::listIdentifiers();
            $utcTime = new DateTime('now', new DateTimeZone('UTC'));
            $tempTimezones = [];
            $timezoneList = [];

            foreach ($timezoneIdentifiers as $timezoneIdentifier) {
                $currentTimezone = new DateTimeZone($timezoneIdentifier);

                $tempTimezones[] = [
                    'offset' => (int) $currentTimezone->getOffset($utcTime),
                    'identifier' => $timezoneIdentifier,
                ];
            }

            usort($tempTimezones, function ($a, $b) {
                return strcmp($a['identifier'], $b['identifier']);
            });

            foreach ($tempTimezones as $tz) {
                $sign = ($tz['offset'] > 0) ? '+' : '-';
                $offset = gmdate('H:i', abs($tz['offset']));
                $name = str_replace('/', ', ', $tz['identifier']);
                $name = str_replace('_', ' ', $name);
                $name = str_replace('St ', 'St. ', $name);

                $timezoneList[$tz['identifier']] = '(UTC ' . $sign . $offset . ') ' . $name;
            }

            Yii::$app->cache->set('timezoneList', $timezoneList);
        }

        return $timezoneList;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public static function datetimeFormatList()
    {
        $time = time();
        $formatter = Yii::$app->formatter;

        return [
            'short' => $formatter->asDatetime($time, 'short'),
            'medium' => $formatter->asDatetime($time, 'medium'),
            'long' => $formatter->asDatetime($time, 'long'),
            'full' => $formatter->asDatetime($time, 'full'),
        ];
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public static function dateFormatList()
    {
        $time = time();
        $formatter = Yii::$app->formatter;

        return [
            'short' => $formatter->asDate($time, 'short'),
            'medium' => $formatter->asDate($time, 'medium'),
            'long' => $formatter->asDate($time, 'long'),
            'full' => $formatter->asDate($time, 'full'),
        ];
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public static function timeFormatList()
    {
        $time = time();
        $formatter = Yii::$app->formatter;

        return [
            'short' => $formatter->asTime($time, 'short'),
            'medium' => $formatter->asTime($time, 'medium'),
            'long' => $formatter->asTime($time, 'long'),
            'full' => $formatter->asTime($time, 'full'),
        ];
    }

    /**
     * @return array
     */
    public static function dateInputFormatList()
    {
        return [
            'php:Y-m-d' => Yii::t('app', '{format} (year-month-day)', ['format' => date('Y-m-d')]),
            'php:Y.m.d' => Yii::t('app', '{format} (year.month.day)', ['format' => date('Y-m-d')]),
            'php:Y/m/d' => Yii::t('app', '{format} (year/month/day)', ['format' => date('Y/m/d')]),
            'php:d-m-Y' => Yii::t('app', '{format} (day-month-year)', ['format' => date('d-m-Y')]),
            'php:d.m.Y' => Yii::t('app', '{format} (day.month.year)', ['format' => date('d.m.Y')]),
            'php:d/m/Y' => Yii::t('app', '{format} (day/month/year)', ['format' => date('d/m/Y')]),
            'php:m-d-Y' => Yii::t('app', '{format} (month-day-year)', ['format' => date('m-d-Y')]),
            'php:m.d.Y' => Yii::t('app', '{format} (month.day.year)', ['format' => date('m.d.Y')]),
        ];
    }

    /**
     * @return array
     */
    public static function timeInputFormatList()
    {
        return [
            'php:H:i' => Yii::t('app', '24 Hours System'),
            'php:h:i A' => Yii::t('app', '12 Hours System'),
        ];
    }

    /**
     * @return void
     * @throws InvalidConfigException
     */
    protected function initGeneralSection()
    {
        $this->renderer->addFields([
            'website_name' => [
                'label' => Yii::t('app', 'Web Name'),
                'rules' => [
                    'required',
                    'string',
                ],
            ],
            'website_description' => [
                'label' => Yii::t('app', 'Web Description'),
                'rules' => [
                    'safe',
                    'string',
                ],
            ],
            'meta_keyword' => [
                'label' => Yii::t('app', 'Meta Keyword'),
                'rules' => [
                    'safe',
                    'string',
                ],
            ],
            'meta_description' => [
                'label' => Yii::t('app', 'Meta Description'),
                'rules' => [
                    'safe',
                    'string',
                ],
            ],
            'logo' => [
                'label' => Yii::t('app', 'Logo'),
                'rules' => [
                    ['image', 'maxSize' => 8 * 1024 * 1024],
                ],
            ],
            'favicon' => [
                'label' => Yii::t('app', 'Favicon'),
                'rules' => [
                    ['image', 'maxSize' => 8 * 1024 * 1024],
                ],
            ],
            'timezone' => [
                'label' => Yii::t('app', 'Timezone'),
                'rules' => [
                    'required',
                    [
                        'in',
                        'range' => array_keys(self::timezoneList()),
                    ],
                ],
            ],
            'number_format' => [
                'label' => Yii::t('app', 'Number Format'),
                'rules' => [
                    'required',
                    [
                        'in',
                        'range' => array_keys(self::numberFormatList()),
                    ],
                ],
            ],
            'datetime_display_format' => [
                'label' => Yii::t('app', 'Date & Time Format'),
                'rules' => [
                    'required',
                    [
                        'in',
                        'range' => array_keys(self::dateFormatList()),
                    ],
                ],
            ],
            'date_display_format' => [
                'label' => Yii::t('app', 'Date Format'),
                'rules' => [
                    'required',
                    [
                        'in',
                        'range' => array_keys(self::dateFormatList()),
                    ],
                ],
            ],
            'time_display_format' => [
                'label' => Yii::t('app', 'Time Format'),
                'rules' => [
                    'required',
                    [
                        'in',
                        'range' => array_keys(self::timeFormatList()),
                    ],
                ],
            ],
            'date_input_format' => [
                'label' => Yii::t('app', 'Date Format'),
                'rules' => [
                    'required',
                    [
                        'in',
                        'range' => array_keys(self::dateInputFormatList()),
                    ],
                ],
            ],
            'time_input_format' => [
                'label' => Yii::t('app', 'Date Format'),
                'rules' => [
                    'required',
                    [
                        'in',
                        'range' => array_keys(self::timeInputFormatList()),
                    ],
                ],
            ],
        ]);

        $this->renderer->addSubSection('website', [
            'label' => Yii::t('app', 'Website Information'),
            'card' => [
                'icon' => 'i8:internet',
            ],
        ]);

        $this->renderer->addSubSection('internationalization', [
            'label' => Yii::t('app', 'Internationalization'),
            'card' => [
                'icon' => 'i8:map-marker',
            ],
        ]);
    }
}
