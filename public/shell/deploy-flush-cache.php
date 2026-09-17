<?php

//only allow execution of this script from shell
if (PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) die('This script is only allowed to be executed from the terminal.');

//load WordPress core, theme and plugins
require_once(__DIR__ . '/load-wordpress.php');

/**
 * Class DeployFlushCache
 */
class DeployFlushCache
{
    /**
     * DeployFlushCache constructor.
     */
    public function __construct()
    {
        $this->flushAutoptimize();
        $this->flushCometCache();
    }

    /**
     * Flush comet cache
     */
    public function flushCometCache()
    {
        try {
            if (class_exists('comet_cache')) {
                $this->log('The Comet Cache plugin is active. Started wiping cache..');
                $deletedCacheFiles = comet_cache::wipe();
                $this->log(sprintf('Successfully wiped %1d cache files from Comet Cache..', $deletedCacheFiles), "success");
            } else {
                $this->log('The Comet Cache plugin is not active. Aborting..');
            }
        } catch (Exception $exception) {
            $this->log($exception->getMessage(), 'error');
        } catch (Error $error) {
            $this->log($error->getMessage(), 'error');
        }
    }

    /**
     * Flush autoptimize
     */
    public function flushAutoptimize()
    {
        try {
            if (class_exists('autoptimizeCache')) {
                $this->log('The Autoptimize plugin is active..');
                shell_exec(sprintf('rm -rf %1s', WP_CONTENT_DIR . '/cache/autoptimize/*'));
                $this->log('The Autoptimize cache folder was successfully emptied..', 'success');
            } else {
                $this->log('The Autoptimize plugin is not active. Aborting..');
            }
        } catch (Exception $exception) {
            $this->log($exception->getMessage(), 'error');
        } catch (Error $error) {
            $this->log($error->getMessage(), 'error');
        }
    }

    /**
     * Log something to the terminal
     * @param string $text
     * @param string $type
     */
    public function log(string $text, string $type = 'regular')
    {
        $typeString = "\033[01;0m ";
        if ($type === "success") $typeString = "\033[01;32m ";
        elseif ($type === "error") $typeString = "\033[01;31m ";
        $date = $typeString . date_i18n("d-m-Y H:i:s");
        echo $date . ' - ' . $text . "\n";
    }
}

new DeployFlushCache();