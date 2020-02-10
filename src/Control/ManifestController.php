<?php


namespace BimTheBam\WebAppManifest\Control;

use BimTheBam\WebAppManifest\Model\RelatedApplication;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\i18n\i18n;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class ManifestController
 * @package BimTheBam\WebAppManifest\Control
 */
class ManifestController extends Controller
{

    /**
     * @var string
     */
    private static $url_segment = 'web-app-manifest';

    /**
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function index(HTTPRequest $request)
    {
        $response = HTTPResponse::create();
        $response->addHeader('Content-Type', 'application/manifest+json');

        $locale = !empty($requestLocale = $request->requestVar('locale'))
            ? i18n::get_closest_translation($requestLocale)
            : i18n::get_locale();

        /** @var SiteConfig|\BimTheBam\WebAppManifest\Model\Extension\SiteConfig $siteConfig */
        if (($siteConfig = SiteConfig::current_site_config())) {
            $manifest = [
                'locale' => i18n::convert_rfc1766($locale),
                'start_url' => ($startURL = Director::absoluteBaseURL()),
                'scope' => $startURL,
                'background_color' => $siteConfig->WebAppManifestBackgroundColor,
                'theme_color' => $siteConfig->WebAppManifestThemeColor,
                'display' => $siteConfig->WebAppManifestDisplay,
            ];

            if (!empty($name = $siteConfig->WebAppManifestName)) {
                $manifest['name'] = $name;
            }

            if (!empty($shortName = $siteConfig->WebAppManifestShortName)) {
                $manifest['short_name'] = $shortName;
            }

            if (!empty($description = $siteConfig->WebAppManifestDescription)) {
                $manifest['description'] = $description;
            }

            if (($icon = $siteConfig->WebAppManifestIcon()) && $icon->exists()) {
                $icons = [];

                if (($iconSizes = Config::inst()->get('WebAppManifest', 'icon_sizes')) && is_array($iconSizes)) {
                    $icons = array_merge($icons, $this->generateIcons($icon, $iconSizes));
                }

                if (($iconSizes = Config::inst()->get('WebAppManifest', 'ios_icon_sizes')) && is_array($iconSizes)) {
                    $icons = array_merge($icons, $this->generateIcons($icon, $iconSizes));
                }

                if (!empty($icons)) {
                    $manifest['icons'] = $icons;
                }
            }

            if (($relatedApplications = $siteConfig->RelatedApplications()) && $relatedApplications->count()) {
                $relatedApplications->each(function (RelatedApplication $application) use (&$manifest) {
                    if (array_key_exists('related_applications', $manifest)) {
                        $manifest['related_applications'] = [];
                    }

                    $data = [
                        'platform' => $application->Platform,
                        'url' => $application->URL,
                    ];

                    if (!empty($id = $application->ApplicationID)) {
                        $data['id'] = $id;
                    }

                    $manifest['related_applications'][] = $data;
                });
            }

            $response->setBody(json_encode($manifest, JSON_UNESCAPED_SLASHES));
        }

        return $response;
    }

    /**
     * @param Image $source
     * @param array $sizes
     * @return array
     */
    protected function generateIcons(Image $source, array $sizes)
    {
        $mimeType = $source->getMimeType();
        $return = [];

        foreach ($sizes as $size) {
            $return[] = [
                'src' => $source->FillMax($size, $size)->getAbsoluteURL(),
                'sizes' => $size . 'x' . $size,
                'type' => $mimeType
            ];
        }

        return $return;
    }
}
