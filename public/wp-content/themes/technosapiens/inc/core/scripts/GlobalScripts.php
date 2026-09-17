<?php

namespace TechnoSapiens\Core;

use TechnoSapiens\Core;

/**
 * class GlobalScripts
 * @package TechnoSapiens\Core
 */
class GlobalScripts extends Singleton {

    /**
     * Define asset handles as constants
     * @var string
     */
    const TS_DIALOG_HANDLE = 'ts-dialog';
    const TS_VIDEO_MODAL_HANDLE = 'ts-video-modal';
    const TS_INLINE_VIDEO_HANDLE = 'ts-inline-video';
    const TS_ACCORDION_HANDLE = 'ts-accordion';
    const TS_LINK_SNIPPET_HANDLE = 'ts-link-snippet';

    /**
     * GlobalScripts constructor
     */
    protected function __construct() {

        //maybe globally init dialog assets
        $globallyEnableDialogAssets = apply_filters('ts_global_enable_core_dialog_assets', true);
        if ($globallyEnableDialogAssets) $this->initCoreDialogAssets();

        //maybe globally init video modal assets
        $globallyEnableVideoModalAssets = apply_filters('ts_global_enable_core_video_modal_assets', false);
        if ($globallyEnableVideoModalAssets) $this->initCoreVideoModalAssets();

        //maybe globally init inline video assets
        $globallyEnableInlineVideoAssets = apply_filters('ts_global_enable_core_inline_video_assets', false);
        if ($globallyEnableInlineVideoAssets) $this->initCoreInlineVideoAssets();

        //maybe globally init accordion assets
        $globallyEnableAccordionAssets = apply_filters('ts_global_enable_core_accordion_assets', false);
        if ($globallyEnableAccordionAssets) $this->initCoreAccordionAssets();

        //maybe globally init link snippet assets
        $globallyEnableLinkSnippetAssets = apply_filters('ts_global_enable_core_link_snippet_assets', false);
        if ($globallyEnableLinkSnippetAssets) $this->initCoreLinkSnippetAssets();
    }

    /**
     * Init dialog assets
     * @return void
     */
    public function initCoreDialogAssets(): void {
        add_action('wp_enqueue_scripts', [$this, 'enqueueCoreDialogAssets'], 9);
        add_action('enqueue_block_assets', function () {
            if (is_admin()) {
                $this->enqueueCoreDialogAssets();
            }
        }, 9);
    }

    /**
     * Init video modal assets
     * @return void
     */
    public function initCoreVideoModalAssets(): void {
        add_action('wp_enqueue_scripts', [$this, 'enqueueCoreVideoModalAssets'], 9);
        add_action('enqueue_block_assets', function () {
            if (is_admin()) {
                $this->enqueueCoreVideoModalAssets();
            }
        }, 9);
    }

    /**
     * Init inline video assets
     * @return void
     */
    public function initCoreInlineVideoAssets(): void {
        add_action('wp_enqueue_scripts', [$this, 'enqueueCoreInlineVideoAssets'], 9);
        add_action('enqueue_block_assets', function () {
            if (is_admin()) {
                $this->enqueueCoreInlineVideoAssets();
            }
        }, 9);
    }

    /**
     * Init accordion assets
     * @return void
     */
    public function initCoreAccordionAssets(): void {
        add_action('wp_enqueue_scripts', [$this, 'enqueueCoreAccordionAssets'], 9);
        add_action('enqueue_block_assets', function () {
            if (is_admin()) {
                $this->enqueueCoreAccordionAssets();
            }
        }, 9);
    }

    /**
     * Init link snippet assets
     * @return void
     */
    public function initCoreLinkSnippetAssets(): void {
        add_action('wp_enqueue_scripts', [$this, 'enqueueCoreLinkSnippetAssets'], 9);
        add_action('enqueue_block_assets', function () {
            if (is_admin()) {
                $this->enqueueCoreLinkSnippetAssets();
            }
        }, 9);
    }

    /**
     * Enqueue dialog assets
     * @return void
     */
    public function enqueueCoreDialogAssets(): void {
        $dialogJs = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'Dialog.js');
        $initDialogsJs = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'initDialogs.js');
        if ($dialogJs && $initDialogsJs) {
            wp_enqueue_script(self::TS_DIALOG_HANDLE, $dialogJs, [], null, true);
            wp_enqueue_script(self::TS_DIALOG_HANDLE . '-init', $initDialogsJs, [self::TS_DIALOG_HANDLE], null, true);
        }
    }

    /**
     * Enqueue video modal assets
     * @return void
     */
    public function enqueueCoreVideoModalAssets(): void {

        //enqueue video modal styles
        $modalCss = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'component-video-modal.scss');
        if ($modalCss) wp_enqueue_style(self::TS_VIDEO_MODAL_HANDLE, $modalCss);

        //handle video modal JS
        $videoModalJs = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'VideoModal.js');
        $initVideoModalsJs = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'initVideoModals.js');
        if ($videoModalJs && $initVideoModalsJs) {
            self::enqueueCoreDialogAssets();
            wp_enqueue_script(self::TS_VIDEO_MODAL_HANDLE, $videoModalJs, [self::TS_DIALOG_HANDLE], null, true);
            wp_enqueue_script(self::TS_VIDEO_MODAL_HANDLE . '-init', $initVideoModalsJs, [self::TS_VIDEO_MODAL_HANDLE], null, true);
            wp_localize_script(self::TS_VIDEO_MODAL_HANDLE, 'wcpPluginVideoModalTranslations', [
                'videoModalLabel' => __("Watch video", Core::TEXT_DOMAIN),
                'videoModalClose' => __("Close", Core::TEXT_DOMAIN)
            ]);
        }
    }


    /**
     * Enqueue inline video assets
     * @return void
     */
    public function enqueueCoreInlineVideoAssets(): void {
        $inlineVideoJs = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'InlineVideo.js');
        $initInlineVideoJs = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'initInlineVideos.js');
        if ($inlineVideoJs && $initInlineVideoJs) {
            wp_enqueue_script(self::TS_INLINE_VIDEO_HANDLE, $inlineVideoJs, [], null, true);
            wp_enqueue_script(self::TS_INLINE_VIDEO_HANDLE . '-init', $initInlineVideoJs, [self::TS_INLINE_VIDEO_HANDLE], null, true);
        }
    }

    /**
     * Enqueue accordion assets
     * @return void
     */
    public function enqueueCoreAccordionAssets(): void {
        $accordionJs = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'Accordion.js');
        $initAccordionJs = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'initAccordions.js');
        if ($accordionJs && $initAccordionJs) {
            wp_enqueue_script(self::TS_ACCORDION_HANDLE, $accordionJs, [], null, true);
            wp_enqueue_script(self::TS_ACCORDION_HANDLE . '-init', $initAccordionJs, [self::TS_ACCORDION_HANDLE], null, true);
        }
    }

    /**
     * Enqueue link snippet assets
     * @return void
     */
    public function enqueueCoreLinkSnippetAssets(): void {

        //enqueue video modal styles
        $linkSnippetCss = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'component-link-snippet.scss');
        if ($linkSnippetCss) wp_enqueue_style(self::TS_LINK_SNIPPET_HANDLE, $linkSnippetCss);

        //handle link snippet JS
        $linkSnippetJs = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'LinkSnippet.js');
        $initLinkSnippetsJs = Enqueue::getWebpackAssetUrlByKey(WCP_MANIFEST_PATH, 'initLinkSnippets.js');
        if ($linkSnippetJs && $initLinkSnippetsJs) {
            wp_enqueue_script(self::TS_LINK_SNIPPET_HANDLE, $linkSnippetJs, [], null, true);
            wp_enqueue_script(self::TS_LINK_SNIPPET_HANDLE . '-init', $initLinkSnippetsJs, [self::TS_LINK_SNIPPET_HANDLE], null, true);
        }
    }
}