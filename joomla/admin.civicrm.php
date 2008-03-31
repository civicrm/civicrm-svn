<?php

if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) ) {
	die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
}

// check for php version and ensure its greater than 5.
// do a fatal exit if
if ( (int ) substr( PHP_VERSION, 0, 1 ) < 5 ) {
    echo "CiviCRM requires PHP Version 5.2 or greater. You are running PHP Version " . PHP_VERSION . "<p>";
    exit( );
}

include_once 'civicrm.settings.php';

require_once 'PEAR.php';

require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Core/Error.php';
require_once 'CRM/Core/Invoke.php';

civicrm_invoke( );

function civicrm_init( ) {
    $config =& CRM_Core_Config::singleton();
    CRM_Core_DAO::init($config->dsn, $config->daoDebug);

    $factoryClass = 'CRM_Contact_DAO_Factory';

    CRM_Core_DAO::setFactory(new $factoryClass());

    // set error handling
    PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array('CRM_Core_Error', 'handle'));
}


function civicrm_invoke( ) {
    civicrm_init( );

    $user = JFactory::getUser( );
    require_once 'CRM/Core/BAO/UFMatch.php';
    CRM_Core_BAO_UFMatch::synchronize( $user, false, 'Joomla', 'Individual' );

    if ( isset( $_GET['task'] ) ) { 
        $args = explode( '/', trim( $_GET['task'] ) );
        CRM_Core_Invoke::invoke( $args );
    }
}


