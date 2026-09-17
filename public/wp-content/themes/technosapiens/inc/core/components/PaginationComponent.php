<?php

namespace TechnoSapiens\Core;

/**
 * class PaginationComponent
 * @package TechnoSapiens\Core
 */
class PaginationComponent extends Singleton {

    /**
     * Get pagination HTML
     * @param string $hash
     * @return string
     */
    public static function getPaginationHtml(string $hash = ''): string {
        return Partial::render('components/pagination', ['hash' => $hash], false, WCP_PARTIAL_PATH);
    }
}