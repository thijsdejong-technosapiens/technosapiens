<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Singleton;

class SectionReferenceEditor extends Singleton {

    protected function __construct() {
        add_action('enqueue_block_assets', [$this, 'enqueueEditorAssets'], 5);
    }

    /**
     * Enqueue dashicons in the block editor iframe for section preview bar icons.
     * @return void
     */
    public function enqueueEditorAssets(): void {
        if (!is_admin()) {
            return;
        }

        wp_enqueue_style('dashicons');
    }
}

SectionReferenceEditor::getInstance();
