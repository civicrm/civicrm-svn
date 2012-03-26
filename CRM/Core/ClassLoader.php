<?php


class CRM_Core_ClassLoader {

    /**
     * Registers this instance as an autoloader.
     *
     * @param Boolean $prepend Whether to prepend the autoloader or not
     *
     * @api
     */
    function register($prepend = false) {
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            spl_autoload_register(array($this, 'loadClass'), true, $prepend);
        }
        else {
            // http://www.php.net/manual/de/function.spl-autoload-register.php#107362
            // "when specifying the third parameter (prepend), the function will fail badly in PHP 5.2"
            spl_autoload_register(array($this, 'loadClass'), true);
        }
    }

    function loadClass($class) {
        if (
            // Only load classes that clearly belong to CiviCRM.
            0 === strncmp($class, 'CRM_', 4) &&
            // Do not load PHP 5.3 namespaced classes.
            // (in a future version, maybe)
            FALSE === strpos($class, '\\')
        ) {
            $file = strtr($class, '_', '/') . '.php';
            // There is some question about the best way to do this.
            // "require_once" is nice because it's simple and throws
            // intelligible errors.  The down side is that autoloaders
            // down the chain cannot try to find the file if we fail.
            require_once($file);
        }
    }
}
