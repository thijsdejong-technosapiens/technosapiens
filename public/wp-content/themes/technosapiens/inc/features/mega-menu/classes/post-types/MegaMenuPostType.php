<?php

namespace TechnoSapiens\MegaMenuPlugin;

use TechnoSapiens\Core\PostType;
use TechnoSapiens\FactoryPlugin\PostTypeFactory;
use TechnoSapiens\MegaMenuPlugin;

/**
 * MegaMenuPostType
 * @package TechnoSapiens\MegaMenuPlugin
 */
final class MegaMenuPostType extends PostTypeFactory {

    /**
     * Define post-type slug
     */
    public const TYPE = 'mega-menu';

    /**
     * MegaMenuPostType constructor
     */
    protected function __construct() {

        //set up post-type labels
        self::setPostTypeLabels(PostType::getLabels(
            __('Mega menu', MegaMenuPlugin::TEXT_DOMAIN),
            __('Mega menus', MegaMenuPlugin::TEXT_DOMAIN)
        ));

        //set up post-type supports
        self::setSupports(['title', 'revisions']);

        //set up post-type icon
        self::setMenuIcon('dashicons-welcome-widgets-menus');

        //set up default post-type features
        self::setFeatures([
            'enableArchive' => false,
            'enableSingle' => false
        ]);

        //call the parent constructor
        parent::__construct();
    }
}