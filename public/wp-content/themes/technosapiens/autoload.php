<?php


//set up array of directories to scan for core PHP files (load order matters: utils first)
$coreDirectoriesToRequire = [
    realpath(__DIR__ . '/inc/core/utils'),
    realpath(__DIR__ . '/inc/core/scripts'),
    realpath(__DIR__ . '/inc/core/components'),
];

foreach ($coreDirectoriesToRequire as $directory) {
    if (!$directory) {
        continue;
    }

    $directoryFiles = scandir($directory);
    if ($directoryFiles) {
        foreach ($directoryFiles as $directoryFile) {
            $filePath = $directory . '/' . $directoryFile;
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            if ($fileExtension === 'php') {
                require_once($filePath);
            }
        }
    }
}

require_once(__DIR__ . '/inc/core/Core.php');

global $corePluginInstance;
if (!$corePluginInstance) {
    $corePluginInstance = \TechnoSapiens\Core::getInstance();
}

if (class_exists('acf_pro')) {
    require_once(__DIR__ . '/inc/features/header-module/autoload.php');
    require_once(__DIR__ . '/inc/features/search/autoload.php');
}

$directoriesToRequire = [
    realpath(__DIR__ . '/inc/factory'),
    realpath(__DIR__ . '/inc/app/utils'),
    realpath(__DIR__ . '/inc/app/scripts'),
    realpath(__DIR__ . '/inc/app/settings'),
    realpath(__DIR__ . '/inc/app/seo'),
    realpath(__DIR__ . '/inc/app/content'),
    realpath(__DIR__ . '/inc/app/editor'),
    realpath(__DIR__ . '/inc/app/integrations'),
    realpath(__DIR__ . '/inc/app/admin'),
    realpath(__DIR__ . '/inc/app/performance'),
    realpath(__DIR__ . '/inc/app/rest'),
    realpath(__DIR__ . '/inc/features/section-reference'),
];

//scan "blocks" directory and add the PHP classes to the array
$blocksDirectory = realpath(__DIR__ . '/blocks');
if ($blocksDirectory) {
    $blockFolders = glob($blocksDirectory . '/*', GLOB_ONLYDIR);
    if ($blockFolders) {
        foreach ($blockFolders as $blockFolder) $directoriesToRequire[] = $blockFolder;
    }
}

//scan "sections" directory and add the PHP classes to the array
$sectionsDirectory = realpath(__DIR__ . '/sections');
if ($sectionsDirectory) {
    $sectionFolders = glob($sectionsDirectory . '/*', GLOB_ONLYDIR);
    if ($sectionFolders) {
        foreach ($sectionFolders as $sectionFolder) {
            $directoriesToRequire[] = $sectionFolder;
        }
    }
}

//scan "post-types" directory and add the PHP classes to the array
$postTypeDirectory = realpath(__DIR__ . '/post-types');
if ($postTypeDirectory) {
    $postTypeFolders = glob($postTypeDirectory . '/*', GLOB_ONLYDIR);
    if ($postTypeFolders) {
        foreach ($postTypeFolders as $postTypeFolder) {
            $directoriesToRequire[] = $postTypeFolder;
        }
    }
}

//autoload the PHP files in the array
foreach ($directoriesToRequire as $directory) {
    if (!$directory) {
        continue;
    }

    $directoryFiles = scandir($directory);
    if ($directoryFiles) {
        foreach ($directoryFiles as $directoryFile) {
            $filePath = $directory . '/' . $directoryFile;
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            if ($fileExtension === 'php') {
                require_once($filePath);
            }
        }
    }
}

if (class_exists('acf_pro') && class_exists('TechnoSapiens\FactoryPlugin\PostTypeFactory')) {
    require_once(__DIR__ . '/inc/features/mega-menu/autoload.php');
}
