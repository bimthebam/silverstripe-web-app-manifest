<?php


namespace BimTheBam\WebAppManifest\Model\Extension;

use BimTheBam\NativeColorInput\Form\Field\ColorField;
use BimTheBam\WebAppManifest\Model\RelatedApplication;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldPageCount;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\ORM\HasManyList;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;

/**
 * Class SiteConfig
 * @package BimTheBam\WebAppManifest\Model\Extension
 * @property \SilverStripe\SiteConfig\SiteConfig|SiteConfig owner
 * @property string WebAppManifestName
 * @property string WebAppManifestShortName
 * @property string WebAppManifestDescription
 * @property string WebAppManifestDisplay
 * @property string WebAppManifestThemeColor
 * @property string WebAppManifestBackgroundColor
 * @property int WebAppManifestIconID
 * @method Image WebAppManifestIcon()
 * @method HasManyList|RelatedApplication[] RelatedApplications()
 */
class SiteConfig extends Extension
{

    /**
     *
     */
    const DISPLAY_BROWSER = 'browser';

    /**
     *
     */
    const DISPLAY_MINIMAL_UI = 'minimal-ui';

    /**
     *
     */
    const DISPLAY_STANDALONE = 'standalone';

    /**
     *
     */
    const DISPLAY_FULLSCREEN = 'fullscreen';

    /**
     * @var array
     */
    private static $db = [
        'WebAppManifestName' => DBVarchar::class,
        'WebAppManifestShortName' => DBVarchar::class,
        'WebAppManifestDescription' => DBText::class,
        'WebAppManifestDisplay' => DBVarchar::class,
        'WebAppManifestThemeColor' => DBVarchar::class,
        'WebAppManifestBackgroundColor' => DBVarchar::class,
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'WebAppManifestIcon' => Image::class,
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'RelatedApplications' => RelatedApplication::class,
    ];

    /**
     * @var array
     */
    private static $owns = [
        'WebAppManifestIcon',
    ];

    /**
     * @var array
     */
    private static $defaults = [
        'WebAppManifestDisplay' => self::DISPLAY_BROWSER,
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $scClass = \SilverStripe\SiteConfig\SiteConfig::class;

        $tab = $fields->findOrMakeTab(
            'Root.WebAppManifest',
            _t($scClass . '.TAB_WEB_APP_MANIFEST', 'WebApp manifest')
        );

        $tab->push(
            TextField::create(
                'WebAppManifestName',
                _t($scClass . '.WEB_APP_MANIFEST_NAME', 'Name')
            )
        );

        $tab->push(
            TextField::create(
                'WebAppManifestShortName',
                _t($scClass . '.WEB_APP_MANIFEST_SHORT_NAME', 'Short name')
            )
        );

        $tab->push(
            TextareaField::create(
                'WebAppManifestDescription',
                _t($scClass . '.WEB_APP_MANIFEST_DESCRIPTION', 'Description')
            )
        );

        $tab->push(
            DropdownField::create(
                'WebAppManifestDisplay',
                _t($scClass . '.WEB_APP_MANIFEST_DISPLAY', 'Display'),
                [
                    self::DISPLAY_BROWSER => _t($scClass . '.WEB_APP_DISPLAY_BROWSER', 'Browser'),
                    self::DISPLAY_MINIMAL_UI => _t($scClass . '.WEB_APP_DISPLAY_MINIMAL_UI', 'Minimal UI'),
                    self::DISPLAY_STANDALONE => _t($scClass . '.WEB_APP_DISPLAY_STANDALONE', 'Standalone'),
                    self::DISPLAY_FULLSCREEN => _t($scClass . '.WEB_APP_DISPLAY_FULLSCREEN', 'Fullscreen'),
                ]
            )
        );

        $tab->push(
            ColorField::create(
                'WebAppManifestThemeColor',
                _t($scClass . '.WEB_APP_MANIFEST_THEME_COLOR', 'Theme color')
            )
        );

        $tab->push(
            ColorField::create(
                'WebAppManifestBackgroundColor',
                _t($scClass . '.WEB_APP_MANIFEST_BACKGROUND_COLOR', 'Background color')
            )
        );

        $tab->push(
            $icon = UploadField::create(
                'WebAppManifestIcon',
                _t($scClass . '.WEB_APP_MANIFEST_ICON', 'Icon')
            )
        );

        $icon->setAllowedExtensions(($allowedExtensions = ['png', 'jpg']));
        $icon->setFolderName('web-app-manifest');
        $icon->setRightTitle(_t(
            $scClass . '.WEB_APP_MANIFEST_ICON_RIGHT_TITLE',
            'Allowed file extensions: {extensions}',
            [
                'extensions' => implode(', ', $allowedExtensions)
            ]
        ))
        ->setDescription(
            _t(
                $scClass . '.WEB_APP_MANIFEST_ICON_DESCRIPTION',
                'For best results use square images only with a minimum length of 1024px.'
            )
        );

        $tab->push(
            GridField::create(
                'RelatedApplications',
                RelatedApplication::singleton()->i18n_plural_name(),
                $this->owner->RelatedApplications(),
                GridFieldConfig_RelationEditor::create()
                    ->removeComponentsByType([
                        GridFieldAddExistingAutocompleter::class,
                        GridFieldDeleteAction::class,
                        GridFieldSortableHeader::class,
                        GridFieldPaginator::class,
                        GridFieldPageCount::class,
                    ])
                    ->addComponents([
                        new GridFieldDeleteAction(),
                        new GridFieldTitleHeader(),
                    ])
            )
        );
    }
}
