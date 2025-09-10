<?php


namespace BimTheBam\WebAppManifest\Model;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class RelatedApplication
 * @package BimTheBam\WebAppManifest\Model
 * @property string Platform
 * @property string URL
 * @property string ApplicationID
 * @property int SiteConfigID
 * @method SiteConfig SiteConfig()
 * @property string PlatformReadable
 * @property array PlatformsMap
 */
class RelatedApplication extends DataObject
{

    /**
     *
     */
    const PLATFORM_CHROME_WEB_STORE = 'chrome_web_store';

    /**
     *
     */
    const PLATFORM_PLAY = 'play';

    /**
     *
     */
    const PLATFORM_ITUNES = 'itunes';

    /**
     *
     */
    const PLATFORM_WINDOWS = 'windows';

    /**
     * @var string
     */
    private static $table_name = 'WebAppManifestRelatedApplication';

    /**
     * @var string
     */
    private static $singular_name = 'Related application';

    /**
     * @var string
     */
    private static $plural_name = 'Related application';

    /**
     * @var array
     */
    private static $db = [
        'Platform' => DBVarchar::class,
        'URL' => DBVarchar::class,
        'ApplicationID' => DBVarchar::class,
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'SiteConfig' => SiteConfig::class,
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'PlatformReadable',
    ];

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([
                'SiteConfigID',
            ]);

            if (($platform = $fields->dataFieldByName('Platform')) && !($platform instanceof DropdownField)) {
                $fields->replaceField(
                    'Platform',
                    $platform = DropdownField::create(
                        'Platform',
                        $platform->Title()
                    )
                );
            }

            $platform->setSource($this->PlatformsMap);
        });

        return parent::getCMSFields();
    }

    /**
     * @param bool $includerelations
     * @return array
     */
    public function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['Platform'] = $labels['PlatformReadable'] = _t(__CLASS__ . '.PLATFORM', 'Platform');
        $labels['URL'] = _t(__CLASS__ . '.URL', 'URL');
        $labels['ApplicationID'] = _t(__CLASS__ . '.APPLICATION_ID', 'Application ID');
        $labels['SiteConfig'] = $labels['SiteConfigID'] = SiteConfig::singleton()->i18n_singular_name();
        return $labels;
    }


    /**
     * @return ValidationResult
     */
    public function validate(): ValidationResult
    {
        $result = parent::validate();

        foreach (['Platform', 'URL'] as $fieldName) {
            if (empty($this->{$fieldName})) {
                $result->addFieldError(
                    $fieldName,
                    _t(__CLASS__ . '.ERROR_EMPTY_FIELD', 'This field is required.')
                );
            }
        }

        $filter = [
            'ID:not' => $this->ID,
            'SiteConfigID' => $this->SiteConfigID,
        ];

        if (static::get()->filter($filter)->find('Platform', $this->Platform)) {
            $result->addFieldError(
                'Platform',
                _t(__CLASS__ . '.ERROR_EXISTS', 'A record for this platform already exists.')
            );
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getPlatformReadable()
    {
        if (array_key_exists($this->Platform, $this->PlatformsMap)) {
            return $this->PlatformsMap[$this->Platform];
        }
        return '';
    }

    /**
     * @return array
     */
    public function getPlatformsMap()
    {
        return [
            self::PLATFORM_CHROME_WEB_STORE => _t(__CLASS__ . '.PLATFORM_CHROME_WEB_STORE', 'Google Chrome web store'),
            self::PLATFORM_PLAY => _t(__CLASS__ . '.PLATFORM_PLAY', 'Google Play store'),
            self::PLATFORM_ITUNES => _t(__CLASS__ . '.PLATFORM_ITUNES', 'iTunes App Store'),
            self::PLATFORM_WINDOWS => _t(__CLASS__ . '.PLATFORM_WINDOWS', 'Windows App Store'),
        ];
    }
}
