<?php
/**
 * @package CiviCRM
 * @version 3.2.0
 */
/*
Plugin Name: CiviCRM
Plugin URI: http://civicrm.org/
Description: CiviCRM WP Plugin
Author: CiviCRM LLC
Version: 4.1.0
Author URI: http://civicrm.org/
License: AGPL3
*/

define('WP_DEBUG', true);

function civicrm_wp_add_menu_items( ) {
    add_menu_page( 'CiviCRM', 'CiviCRM', 'edit_posts', 'CiviCRM', 'civicrm_wp_invoke' );
}

function civicrm_wp_set_title( $title = '' ) {
    global $civicrm_wp_title;
    return empty( $civicrm_wp_title ) ? $title : $civicrm_wp_title;
}

function civicrm_wp_initialize( ) {

    static $initialized    = false;
    static $failure        = false;

    if ( $failure ) {
        return false;
    }

    if ( ! $initialized ) {
        // Check for php version and ensure its greater than 5.
        // do a fatal exit if
        if ( (int ) substr( PHP_VERSION, 0, 1 ) < 5 ) {
            echo "CiviCRM requires PHP Version 5.2 or greater. You are running PHP Version " . PHP_VERSION . "<p>";
            exit( );
        }


        $settingsFile = 
            WP_PLUGIN_DIR . DIRECTORY_SEPARATOR .
            'civicrm' . DIRECTORY_SEPARATOR .
            'civicrm.settings.php';
        $error = include_once( $settingsFile );

	    // get ready for problems
        $docLinkInstall = "http://wiki.civicrm.org/confluence/display/CRMDOC/Drupal+Installation+Guide";
        $docLinkTrouble = "http://wiki.civicrm.org/confluence/display/CRMDOC/Installation+and+Configuration+Trouble-shooting";
        $forumLink      = "http://forum.civicrm.org/index.php/board,6.0.html";

	    $errorMsgAdd = t("Please review the <a href='!1'>Drupal Installation Guide</a> and the <a href='!2'>Trouble-shooting page</a> for assistance. If you still need help installing, you can often find solutions to your issue by searching for the error message in the <a href='!3'>installation support section of the community forum</a>.</strong></p>", 
                         array('!1' => $docLinkInstall, '!2' => $docLinkTrouble, '!3' => $forumLink ) );
        

        if ( $error == false ) {
            $failure = true;
	        drupal_set_message( "<strong><p class='error'>" . 
                                t("Oops! - The CiviCRM settings file (civicrm.settings.php) was not found in the expected location ") . 
                                "(" . $settingsFile . "). </p><p class='error'>" .
                                $errorMsgAdd . '</p></strong>' );
            return false;
        }
        
        // this does pretty much all of the civicrm initialization
        $error = include_once( 'CRM/Core/Config.php' );
        if ( $error == false ) {
            $failure = true;
            drupal_set_message( "<strong><p class='error'>" . 
                                t("Oops! - The path for including CiviCRM code files is not set properly. Most likely there is an error in the <em>civicrm_root</em> setting in your CiviCRM settings file (!1).", 
                                   array( '!1' => $settingsFile ) ) .
                                "</p><p class='error'> &raquo; " . 
                                t("civicrm_root is currently set to: <em>!1</em>.", array( '!1' => $civicrm_root ) ) . 
                                "</p><p class='error'>" .  $errorMsgAdd . "</p></strong>" );
            return false;
        }

        $initialized = true;

        // initialize the system by creating a config object
        $config =& CRM_Core_Config::singleton();

        $session =& CRM_Core_Session::singleton( );
        $session->set( 'userID', 1 );
    }

    return true;
}

function civicrm_wp_invoke( ) {
    if ( ! civicrm_wp_initialize( ) ) {
        return;
    }

    if ( isset( $_GET['q'] ) ) {
        $args = explode( '/', trim( $_GET['q'] ) );
    } else {
        $_GET['q'] = 'civicrm/dashboard';
        $_GET['reset'] = 1;
        $args = array( 'civicrm', 'dashboard' );
    }
    require_once 'CRM/Core/Invoke.php';
    CRM_Core_Invoke::invoke( $args );
}

function civicrm_wp_scripts( ) {
    if ( ! civicrm_wp_initialize( ) ) {
        return;
    }
    
    require_once 'CRM/Core/Smarty.php';
    $template =& CRM_Core_Smarty::singleton( );
    $buffer = $template->fetch( 'CRM/common/jquery.files.tpl' );
    $lines  = preg_split( '/\s+/', $buffer );
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }
        if ( strpos( $line, '.js' ) !== false ) {
            wp_enqueue_script( $line, WP_PLUGIN_URL .  "/CiviCRM/CiviCRM/$line" );
        }
    }
    
    // add Common.js
    wp_enqueue_script( 'js/Common.js', WP_PLUGIN_URL . '/CiviCRM/CiviCRM/js/Common.js' );
    return;
}

function civicrm_wp_styles( ) {
    if ( ! civicrm_wp_initialize( ) ) {
        return;
    }
    
    require_once 'CRM/Core/Smarty.php';
    $template =& CRM_Core_Smarty::singleton( );
    $buffer = $template->fetch( 'CRM/common/jquery.files.tpl' );
    $lines  = preg_split( '/\s+/', $buffer );
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }
        if ( strpos( $line, '.css' ) !== false ) {
            wp_register_style( $line, WP_PLUGIN_URL . "/CiviCRM/CiviCRM/$line" );
            wp_enqueue_style( $line );
        }
    }

    wp_register_style( 'civicrm/css/deprecate.css', WP_PLUGIN_URL . "/CiviCRM/CiviCRM/css/deprecate.css" );
    wp_enqueue_style( 'civicrm/css/deprecate.css' );
    wp_register_style( 'civicrm/css/civicrm.css', WP_PLUGIN_URL . "/CiviCRM/CiviCRM/css/civicrm.css" );
    wp_enqueue_style( 'civicrm/css/civicrm.css' );
    wp_register_style( 'civicrm/css/extras.css', WP_PLUGIN_URL . "/CiviCRM/CiviCRM/css/extras.css" );
    wp_enqueue_style( 'civicrm/css/extras.css' );

    return;
}

function civicrm_wp_main( ) {
    add_action( 'admin_print_styles' , 'civicrm_wp_styles' );
    add_action( 'wp_print_scripts', 'civicrm_wp_scripts' );

    add_action( 'admin_menu', 'civicrm_wp_add_menu_items' );

    add_filter( 'the_title' , 'civicrm_wp_set_title' );
    add_filter( 'wp_title'  , 'civicrm_wp_set_title' );
}

function civicrm_wp_in_civicrm( ) {
    return ( isset( $_GET['page'] ) &&
             $_GET['page'] == 'CiviCRM' ) ? true : false;
}

function drupal_get_breadcrumb( ) {
    return;
}

function drupal_set_breadcrumb( ) {
    return;
}

function t( $str ) {
    return $str;
}

civicrm_wp_main( );