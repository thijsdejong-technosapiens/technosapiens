<?php

namespace TechnoSapiens;

use TechnoSapiens\Core\Log;
use TechnoSapiens\Core\Singleton;

/**
 * Class OpcacheComponent
 * @package TechnoSapiens
 */
class OpcacheComponent extends Singleton {

    /**
     * OpcacheComponent constructor
     */
    protected function __construct() {
        if (function_exists('\opcache_reset') && PHP_SAPI !== 'cli') {
            add_action('admin_init', function () {
                if (get_option('queue_opcache_flush', '0') === '1') {
                    try {
                        if (opcache_reset()) {
                            Log::log('The OPCache was successfully reset..');
                        } else {
                            Log::log('The OPCache was not successfully reset..');
                        }
                        update_option('queue_opcache_flush', '0');
                    } catch (\Exception $exception) {
                        Log::log('An Exception occurred while running OpcacheComponent: ' . $exception->getMessage());
                    } catch (\Error $error) {
                        Log::log('An Error occurred while running OpcacheComponent: ' . $error->getMessage());
                    }
                }
            });
        }
    }
}