<?php

if (!defined('ABSPATH')) {
    exit;
}

$directoriesToRequire = [
    realpath(__DIR__ . '/classes/post-types'),
    realpath(__DIR__ . '/classes/scripts'),
    realpath(__DIR__ . '/classes/components'),
];

$presetDirectory = realpath(__DIR__ . '/presets');
if ($presetDirectory) {
    $presetDirectoryScan = scandir($presetDirectory);
    if ($presetDirectoryScan) {
        foreach ($presetDirectoryScan as $presetDirectoryFile) {
            $possibleDirectory = $presetDirectory . '/' . $presetDirectoryFile;
            if (is_dir($possibleDirectory) && $presetDirectoryFile !== '.' && $presetDirectoryFile !== '..') {
                $directoriesToRequire[] = $possibleDirectory;
            }
        }
    }
}

foreach ($directoriesToRequire as $directory) {
    if (!$directory) {
        continue;
    }

    $directoryFiles = scandir($directory);
    if (!$directoryFiles) {
        continue;
    }

    foreach ($directoryFiles as $directoryFile) {
        $filePath = $directory . '/' . $directoryFile;
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        if ($fileExtension === 'php') {
            require_once($filePath);
        }
    }
}

require_once(__DIR__ . '/MegaMenuPlugin.php');
