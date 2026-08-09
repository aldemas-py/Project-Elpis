<?php

/**
 * Simple PSR-4 style autoloader for PHPMailer classes.
 * PHPMailer is namespaced under `PHPMailer\PHPMailer`.
 *
 * This maps the namespace to the `includes/phpmailer/` directory so we can
 * use PHPMailer without Composer.
 */
spl_autoload_register(function ($class) {
    // PHPMailer namespace prefix
    $prefix = 'PHPMailer\\PHPMailer\\';

    // Does the class use the namespace prefix?
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    // Get the relative class name
    $relativeClass = substr($class, strlen($prefix));

    // Replace namespace separators with directory separators
    $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
