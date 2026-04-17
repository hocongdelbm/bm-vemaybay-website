<?php
spl_autoload_register(function (string $class): void {
    try {
        // Only handle our own namespace prefix
        $prefix = 'custom\\';
        if (strpos($class, $prefix) !== 0) return;

        // Custom\Services\Location\LocationService
        // → custom/Services/Location/LocationService.php
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file      = 'custom/' . $relative . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
    catch (Throwable $th) {
        $GLOBALS['log']->fatal("Exception {$th->getCode()}: {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
    }
});