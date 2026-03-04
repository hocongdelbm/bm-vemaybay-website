<?php
try {
    foreach(['custom/entrypoints/entryNonAuthClass/', 'custom/entrypoints/entryAuthClass/'] as $entry_class_dir) {
        foreach (glob("$entry_class_dir*.php") as $filename) {
            if(preg_match('/^entry.+Class\.php$/', str_replace($entry_class_dir, '', $filename))) require_once $filename;
        }
    }
}
catch (Throwable $th) {
    $GLOBALS['log']->fatal("{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
}

class entryFactory {
    public static function create(string $class_name) {
        if(class_exists($class_name)) $obj = new $class_name();
        return $obj ?? null;
    }
}
