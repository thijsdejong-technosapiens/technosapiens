<?php

namespace TechnoSapiens\Core;

/**
 * Class Partial
 * @package TechnoSapiens\Core
 */
class Partial {
    /**
     * Get or output partial template
     * @param string $file
     * @param array $args
     * @param bool $output
     * @param string $partialPath
     * @param bool $stripComments
     * @return string
     */
    public static function render(string $file, array $args = [], bool $output = true, string $partialPath = '', bool $stripComments = false): string {
        $html = '';

        $isDev = getenv('DEV') === 'true';
        $partialsTemplate = $partialPath ?: get_stylesheet_directory() . '/partials/';

        // format file with extension
        $rawFile = $file;
        $file = !preg_match('#\.php$#', $file) ? $file . '.php' : $file;

        // check if file exist, if so include template with passed arguments
        if (!file_exists($partialsTemplate . $file)) {
            Log::log(sprintf('file %1s does not exist in given partials template directory %2s', $file, $partialsTemplate));
        } else {
            ob_start();
            extract($args);
            include($partialsTemplate . $file);
            $html = ob_get_clean();
        }

        //allow overruling partial templates
        $html = apply_filters('ts_partial_render', $html, $rawFile, $args, $output, $partialPath);

        //add comments for debugging purposes
        if ($isDev && !empty($html) && !$stripComments) $html = '<!--- Start partial: ' . $partialsTemplate . $file . ' --->' . $html;
        if ($isDev && !empty($html) && !$stripComments) $html = $html . '<!--- End partial: ' . $partialsTemplate . $file . ' --->';

        // echo the output when is enabled
        if ($output && !empty($html)) echo $html;

        return $html ?: '';
    }
}