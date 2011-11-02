<?php

function loadCivicrm() {
    static $authenticated;
    // session_start();
    // session_destroy( );
    // exit;
    if (!isset($authenticated)) {       

        $current_path = getcwd();
        $civicrm_root = dirname(dirname(getcwd()));
        $authenticated = true;
        require_once "{$civicrm_root}/civicrm.config.php";
        require_once 'CRM/Core/Config.php';
        
        $config = CRM_Core_Config::singleton();
        
        require_once 'CRM/Utils/System.php';

        CRM_Utils_System::loadBootStrap(array(), false, false);
        // global $user;
        // print_r( $user);
        // exit;
        /*
        $cmsPath = '/var/www/trunk';
        
        // load drupal bootstrap
        chdir($cmsPath);
        define('DRUPAL_ROOT', $cmsPath);

        // FIX ME
        global $base_url;
        $base_url = 'http://localhost/trunk';

        session_start();
        require_once 'includes/bootstrap.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        */

        if ($config->userFramework == 'Drupal' &&
            !user_access('access CiviCRM') ) {
            return;
        }
        
        if (!isset($_SESSION['KCFINDER'])) {
            $_SESSION['KCFINDER'] = array();
            $_SESSION['KCFINDER']['disabled'] = false;
        }
        
        // User has permission, so make sure KCFinder is not disabled!
        if (!isset($_SESSION['KCFINDER']['disabled'])) {
            $_SESSION['KCFINDER']['disabled'] = false;
        }
        
        $_SESSION['KCFINDER']['uploadURL'] = 'http://localhost/trunk/sites/default/files/civicrm/persist/contribute';
        $_SESSION['KCFINDER']['uploadDir'] = '/var/www/trunk/sites/default/files/civicrm/persist/contribute';
        $_SESSION['KCFINDER']['theme'] = 'oxygen';
        
        chdir($current_path );
        return true;
    }
 }

loadCivicrm( );
spl_autoload_register('__autoload');

?>