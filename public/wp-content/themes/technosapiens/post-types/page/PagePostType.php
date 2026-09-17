<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;
use TechnoSapiens\GlobalCtaPlugin\AcfLocationComponent as GlobalCtaAcfLocationComponent;
use TechnoSapiens\HeaderModulePlugin\AcfLocationComponent as HeaderModuleAcfLocationComponent;

/**
 * Class PagePostType
 * @package TechnoSapiens
 */
class PagePostType extends Singleton {

    /**
     * Define post type slug
     */
    const TYPE = 'page';

    /**
     * PagePostType constructor.
     */
    protected function __construct() {

        //add header module to post type
        if (class_exists('TechnoSapiens\HeaderModulePlugin')) {
            HeaderModuleAcfLocationComponent::getInstance()->addHeaderModuleToPostType(self::TYPE);
        }

        //add global CTA to post type
        if (class_exists('TechnoSapiens\GlobalCtaPlugin')) {
            GlobalCtaAcfLocationComponent::getInstance()->addGlobalCtaExcludeSettingsToPostType(self::TYPE);
        }
    }
}