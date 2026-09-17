<?php

namespace TechnoSapiens\Core;

/**
 * class JsonFetchComponent
 * @package TechnoSapiens\Core
 */
class JsonFetchComponent extends Singleton {
    /**
     * Set default value for files array
     * @var array ]
     */
    protected array $files = [];

    /**
     * Fetch contents of a JSON file
     * @param string $path
     * @return \stdClass|false
     */
    public function fetch(string $path): \stdClass|false {
        //set default value
        $content = false;

        //bail if no path given
        if (!$path) return $content;

        //prevent redundant JSON fetching
        if (array_key_exists($path, $this->files)) return $this->files[$path];

        try {
            $rawJson = @file_get_contents($path);
            if ($rawJson) {
                $parsedJson = json_decode($rawJson);
                if (is_a($parsedJson, '\StdClass')) {
                    $content = $parsedJson;
                    $this->files[$path] = $parsedJson;
                }
            }
        } catch (\Exception $exception) {
            Log::log(sprintf('Failed fetching JSON wil with path %1s. Error message: %2s', $path, $exception->getMessage()));
        }

        return $content;
    }

    /**
     * Deep merge two stdClass objects recursively
     * - Arrays are replaced (not merged)
     * - Objects are recursively merged
     * - Scalars are replaced
     * @param \stdClass $base Base configuration
     * @param \stdClass $override Override configuration
     * @return \stdClass Merged configuration
     */
    public function deepMerge(\stdClass $base, \stdClass $override): \stdClass {

        //clone base to avoid modifying the original
        $result = clone $base;

        //iterate over all properties in the override
        foreach ($override as $key => $value) {

            //if the property doesn't exist in base, just add it
            if (!property_exists($result, $key)) {
                $result->{$key} = $value;
                continue;
            }

            //if both are objects, recursively merge
            if (is_object($value) && is_object($result->{$key})) {
                $result->{$key} = $this->deepMerge($result->{$key}, $value);
            } else {
                //for arrays and scalars, override completely replaces base
                $result->{$key} = $value;
            }
        }

        return $result;
    }
}