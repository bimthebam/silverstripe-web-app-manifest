<?php


namespace BimTheBam\WebAppManifest\Model\Extension;

use BimTheBam\WebAppManifest\Control\ManifestController;
use BimTheBam\WebAppManifest\Model\RelatedApplication;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;

/**
 * Class SiteTree
 * @package BimTheBam\WebAppManifest\Model\Extension
 */
class SiteTree extends Extension
{

    /**
     * @param array $tags
     */
    public function updateMetaComponents(array &$tags)
    {
        /** @var \SilverStripe\SiteConfig\SiteConfig|SiteConfig $siteConfig */
        if (!($siteConfig = \SilverStripe\SiteConfig\SiteConfig::current_site_config())) {
            return;
        }

        $tags['manifest'] = [
            'tag' => 'link',
            'attributes' => [
                'rel' => 'manifest',
                'href' => ManifestController::singleton()->Link(),
            ],
        ];

        if (($icon = $siteConfig->WebAppManifestIcon()) && $icon->exists()) {
            $iconType = 'image/' . $icon->getExtension();

            $tags['shortcutIcon'] = [
                'tag' => 'link',
                'attributes' => [
                    'rel' => 'shortcut icon',
                    'type' => $iconType,
                    'href' => $icon->FillMax(32, 32)->getAbsoluteURL(),
                ]
            ];

            if (($iconSizes = Config::inst()->get('WebAppManifest', 'icon_sizes')) && is_array($iconSizes)) {
                $tags = array_merge($tags, $this->generateIconTags($icon, $iconSizes));
            }

            if (($iconSizes = Config::inst()->get('WebAppManifest', 'ios_icon_sizes')) && is_array($iconSizes)) {
                $tags = array_merge($tags, $this->generateIconTags($icon, $iconSizes, 'apple-touch-icon'));
            }
        }

        $tags['themeColor'] = [
            'tag' => 'meta',
            'attributes' => [
                'name' => 'theme-color',
                'content' => $siteConfig->WebAppManifestThemeColor,
            ],
        ];

        /** @var RelatedApplication $iosApp */
        $iosApp = $siteConfig->RelatedApplications()->find('Platform', RelatedApplication::PLATFORM_ITUNES);

        if ($iosApp && !empty($id = $iosApp->ApplicationID)) {
            $tags['ios'] = [
                'type' => 'meta',
                'attributes' => [
                    'name' => 'apple-itunes-app',
                    'content' => 'app-id=' . $id,
                ],
            ];
        }
    }

    /**
     * @param Image $icon
     * @param array $sizes
     * @param string $rel
     * @return array
     */
    protected function generateIconTags(Image $icon, array $sizes, string $rel = 'icon')
    {
        $tags = [];

        foreach ($sizes as $size) {
            $tags['icon' . $size] = [
                'tag' => 'link',
                'attributes' => [
                    'rel' => $rel,
                    'type' => 'image/' . $icon->getExtension(),
                    'size' => $size . 'x' . $size,
                    'href' => $icon->FillMax($size, $size)->getAbsoluteURL(),
                ],
            ];
        }

        return $tags;
    }
}
