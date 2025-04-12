<?php
// autoload.php

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/controller/',
        __DIR__ . '/model/',
        __DIR__ . '/view/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
