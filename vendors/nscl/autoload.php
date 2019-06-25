<?php

namespace NSCL;

spl_autoload_register(function ($className) {
    // "Namespace\Subpackage\ClassX"
    $className = ltrim($className, '\\');

    if (strpos($className, __NAMESPACE__) !== 0) {
        return false;
    }

    // "Subpackage\ClassX"
    $pluginFile = str_replace(__NAMESPACE__ . '\\', '', $className);
    // "Subpackage/ClassX"
    $pluginFile = str_replace('\\', DIRECTORY_SEPARATOR, $pluginFile);
    // "Subpackage/ClassX.php"
    $pluginFile .= '.php';
    // "/path/to/root/folder/Subpackage/ClassX.php"
    $pluginFile = __DIR__ . DIRECTORY_SEPARATOR . $pluginFile;

    if (file_exists($pluginFile)) {
        require $pluginFile;
        return true;
    } else {
        return false;
    }
});
