<?php

if (!defined('ABSPATH')) {
    exit;
}

$directoriesToRequire = [
    realpath(__DIR__ . '/classes/scripts'),
    realpath(__DIR__ . '/classes/settings'),
    realpath(__DIR__ . '/classes/components'),
];

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

require_once(__DIR__ . '/SearchPlugin.php');
