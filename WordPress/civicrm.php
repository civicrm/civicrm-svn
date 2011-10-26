<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/
/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
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

// there is no session handling in WP hence we start it for CiviCRM pages
if ( ! session_id( ) ) {
    session_start( );
    // print_r( $_SESSION );
}

//this is require for ajax calls in civicrm
if ( civicrm_wp_in_civicrm() ) {
    $_GET['noheader'] = true;
} else {
    $_GET['mode'] = 'wordpress';
}

define('WP_DEBUG', true);

function civicrm_wp_add_menu_items( ) {
    add_menu_page( 'CiviCRM', 'CiviCRM', 'edit_posts', 'CiviCRM', 'civicrm_wp_invoke' );
    add_options_page( 'CiviCRM Settings', 'CiviCRM Settings', 'manage_options', 'civicrm-settings', 'civicrm_db_settings');
}

function civicrm_db_settings( ) {
    $installFile = 
            WP_PLUGIN_DIR . DIRECTORY_SEPARATOR .         
            'civicrm' . DIRECTORY_SEPARATOR .
            'civicrm' . DIRECTORY_SEPARATOR .
            'install' . DIRECTORY_SEPARATOR .
            'index.php';
    include( $installFile );
}

function civicrm_wp_set_title( $title = '' ) {
    global $civicrm_wp_title;
    return empty( $civicrm_wp_title ) ? $title : $civicrm_wp_title;
}

function civicrm_setup_warning( ) {
    $installLink = admin_url() . "options-general.php?page=civicrm-settings";
    echo '<div id="civicrm-warning" class="updated fade"><p><strong>' .
    t( 'CiviCRM is almost ready.' ). '</strong> ' .
    t( 'You must <a href="!1">configure CiviCRM</a> for it to work.', array( '!1' => $installLink) )    .
    '</p></div>';
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

        $settingsFile = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR .
                        'civicrm' . DIRECTORY_SEPARATOR .
                        'civicrm.settings.php';

        $error = include_once( $settingsFile );

        // get ready for problems
        $installLink    = admin_url() . "options-general.php?page=civicrm-settings";
        $docLinkInstall = "http://wiki.civicrm.org/confluence/display/CRMDOC/WordPress+Installation+Guide";
        $docLinkTrouble = "http://wiki.civicrm.org/confluence/display/CRMDOC/Installation+and+Configuration+Trouble-shooting";
        $forumLink      = "http://forum.civicrm.org/index.php/board,6.0.html";

        $errorMsgAdd = t("Please review the <a href='!1'>WordPress Installation Guide</a> and the <a href='!2'>Trouble-shooting page</a> for assistance. If you still need help installing, you can often find solutions to your issue by searching for the error message in the <a href='!3'>installation support section of the community forum</a>.</strong></p>", 
                         array('!1' => $docLinkInstall, '!2' => $docLinkTrouble, '!3' => $forumLink ) );
        
        $installMessage = t("Click <a href='!1'>here</a> for fresh install.", array( '!1' => $installLink ) );  
            
        if ( $error == false ) {
            header( 'Location: ' . admin_url() . 'options-general.php?page=civicrm-settings' );
            return false;
        }
        
        // this does pretty much all of the civicrm initialization
        $error = include_once( 'CRM/Core/Config.php' );
        if ( $error == false ) {
            $failure = true;
            //FIX ME
            wp_die( "<strong><p class='error'>" . 
                                t("Oops! - The path for including CiviCRM code files is not set properly. Most likely there is an error in the <em>civicrm_root</em> setting in your CiviCRM settings file (!1).", 
                                   array( '!1' => $settingsFile ) ) .
                                "</p><p class='error'> &raquo; " . 
                                t("civicrm_root is currently set to: <em>!1</em>.", array( '!1' => $civicrm_root ) ) . 
                                "</p><p class='error'>" .  $errorMsgAdd . "</p></strong>" );
            return false;
        }

        $initialized = true;

        // initialize the system by creating a config object
        $config = CRM_Core_Config::singleton();

        // sync the logged in user with WP
        global $current_user;
        if ( $current_user ) {
            require_once 'CRM/Core/BAO/UFMatch.php';
            CRM_Core_BAO_UFMatch::synchronize( $current_user, false, 'WordPress',
                                               civicrm_get_ctype( 'Individual' ) );
        }

    }

    return true;
}

/**
 * Function to get the contact type
 * @param string $default contact type
 *
 * @return $ctype contact type
 */
function civicrm_get_ctype( $default = null ) 
{
    // here we are creating a new contact
    // get the contact type from the POST variables if any

    if ( isset( $_REQUEST['ctype'] ) ) {
        $ctype = $_REQUEST['ctype'];
    } else if ( isset( $_REQUEST['edit'] ) &&
                isset( $_REQUEST['edit']['ctype'] ) ) {
        $ctype = $_REQUEST['edit']['ctype'];
    } else {
        $ctype = $default;
    }

    if ( $ctype != 'Individual'   &&
         $ctype != 'Organization' &&
         $ctype != 'Household' ) {
        $ctype = $default;
    }
    return $ctype; 
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
   
    global $current_user;
    get_currentuserinfo( );
    
    /* bypass synchronize if running upgrade 
     * to avoid any serious non-recoverable error 
     * which might hinder the upgrade process. 
     */
    require_once 'CRM/Utils/Array.php';
    if ( CRM_Utils_Array::value( 'q', $_GET ) != 'civicrm/upgrade' ) {
        require_once 'CRM/Core/BAO/UFMatch.php';
        CRM_Core_BAO_UFMatch::synchronize( $current_user, false, 'WordPress', 'Individual', true );
    }

    require_once 'CRM/Core/Invoke.php';
    CRM_Core_Invoke::invoke( $args );
}

function civicrm_wp_scripts( ) {
    if ( ! civicrm_wp_initialize( ) ) {
        return;
    }
    
    require_once 'CRM/Core/Smarty.php';
    $template = CRM_Core_Smarty::singleton( );
    $buffer = $template->fetch( 'CRM/common/jquery.files.tpl' );
    $lines  = preg_split( '/\s+/', $buffer );
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }
        if ( strpos( $line, '.js' ) !== false ) {
            wp_enqueue_script( $line, WP_PLUGIN_URL . "/civicrm/civicrm/$line" );
        }
    }
    
    // add Common.js
    wp_enqueue_script( 'js/Common.js', WP_PLUGIN_URL . '/civicrm/civicrm/js/Common.js' );
    return;
}

function civicrm_wp_styles( ) {
    if ( ! civicrm_wp_initialize( ) ) {
        return;
    }
    
    require_once 'CRM/Core/Smarty.php';
    $template = CRM_Core_Smarty::singleton( );
    $buffer = $template->fetch( 'CRM/common/jquery.files.tpl' );
    $lines  = preg_split( '/\s+/', $buffer );
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }
        if ( strpos( $line, '.css' ) !== false ) {
            wp_register_style( $line, WP_PLUGIN_URL . "/civicrm/civicrm/$line" );
            wp_enqueue_style( $line );
        }
    }

    wp_register_style( 'civicrm/css/deprecate.css', WP_PLUGIN_URL . "/civicrm/civicrm/css/deprecate.css" );
    wp_enqueue_style( 'civicrm/css/deprecate.css' );
    wp_register_style( 'civicrm/css/civicrm.css', WP_PLUGIN_URL . "/civicrm/civicrm/css/civicrm.css" );
    wp_enqueue_style( 'civicrm/css/civicrm.css' );
    wp_register_style( 'civicrm/css/extras.css', WP_PLUGIN_URL . "/civicrm/civicrm/css/extras.css" );
    wp_enqueue_style( 'civicrm/css/extras.css' );

    return;
}

function civicrm_wp_frontend( ) {
    if ( ! civicrm_wp_initialize( ) ) {
        return;
    }

    if ( isset( $_GET['q'] ) ) {
        $args = explode( '/', trim( $_GET['q'] ) );
    }
   
    // check permission
    if ( ! civicrm_check_permission( $args ) ) {
        add_filter('the_content', 'civicrm_set_frontendmessage');
        return;
    }

    require_once 'wp-includes/pluggable.php';

    // this places civicrm inside frontend theme
    // wp documentation rocks if you know what you are looking for
    // but best way is to check other plugin implementation :) 
   
    add_filter('the_content', 'civicrm_wp_invoke');
}

function civicrm_set_frontendmessage() {
    return ts('You do not have permission to execute this url.');
}

function civicrm_check_permission( $args ) {
    if ( $args[0] != 'civicrm' ) {
        return false;
    }

    require_once 'CRM/Utils/Array.php';
    // all profile and file urls, as well as user dashboard and tell-a-friend are valid
    $arg1 = CRM_Utils_Array::value( 1, $args );
    $validPaths = array( 'profile', 'user', 'dashboard', 'friend', 'file', 'ajax' );
    if ( in_array( $arg1 , $validPaths ) ) {
        return true;
    }
    
    $config = CRM_Core_Config::singleton( );
    
    // set frontend true 
    $config->userFrameworkFrontend = true;

    $arg2 = CRM_Utils_Array::value( 2, $args );
    $arg3 = CRM_Utils_Array::value( 3, $args );

    // allow editing of related contacts
    if ( $arg1 == 'contact' &&
         $arg2 == 'relatedcontact' ) {
        return true;
    }

    // a contribution page / pcp page
    if ( in_array( 'CiviContribute', $config->enableComponents ) ) {
        if ( $arg1 == 'contribute' &&
            in_array( $arg2, array( 'transact', 'campaign', 'pcp') ) ) {
            return true;
        }
    }

    // an event registration page is valid
    if ( in_array( 'CiviEvent', $config->enableComponents ) ) {
        if ( $arg1 == 'event' &&
             in_array( $arg2, array( 'register', 'info', 'participant', 'ical', 'confirm' ) ) ) {
            return true;
        }

        // also allow events to be mapped
        if ( $arg1 == 'contact' &&
             $arg2 == 'map'     &&
             $arg3 == 'event'   ) {
            return true;
        }
    }
    
    // allow mailing urls to be processed
    if ( $arg1 == 'mailing' &&
         in_array( 'CiviMail', $config->enableComponents ) ) {
        if ( in_array( $arg2,
                       array( 'forward', 'unsubscribe', 'resubscribe', 'optout', 'subscribe', 'confirm' ) ) ) {
            return true;
        }
    }

    // allow petition sign in, CRM-7401
    if ( in_array( 'CiviCampaign', $config->enableComponents ) ) {
        if ( $arg1 == 'petition' &&
             $arg2 == 'sign' ) {
            return true;
        }
    }

    return false;
}


function civicrm_wp_main( ) {
    if ( is_admin() ) {
        add_action( 'admin_menu', 'civicrm_wp_add_menu_items' );

        // check if settings file exist, do not show configuration link on
        // install / settings page
        if ( isset( $_GET['page'] ) && $_GET['page'] != 'civicrm-settings' ) {
            $settingsFile = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 
                'civicrm' . DIRECTORY_SEPARATOR .'civicrm.settings.php';
        
            if ( !file_exists( $settingsFile ) ) {
                add_action( 'admin_notices', 'civicrm_setup_warning' );
            }
        }
    }
  
    add_action( 'user_register'   , 'civicrm_user_register'  );
    add_action( 'profile_update'  , 'civicrm_profile_update' );

    if ( ! civicrm_wp_in_civicrm( ) ) {
        return;
    }

    if ( ! is_admin( ) ) {
        add_action( 'wp_print_styles' , 'civicrm_wp_styles' );
 
        add_action('wp_footer', 'civicrm_buffer_end');

        // we do this here rather than as an action, since we dont control
        // the order
        civicrm_buffer_start( );

        civicrm_wp_frontend();
    } else {
        add_action( 'admin_print_styles' , 'civicrm_wp_styles' );
    }

    add_action( 'wp_print_scripts', 'civicrm_wp_scripts' );
 
    add_filter( 'the_title' , 'civicrm_wp_set_title' );
    add_filter( 'wp_title'  , 'civicrm_wp_set_title' );

}

function civicrm_wp_in_civicrm( ) {
    return ( isset( $_GET['page'] ) &&
             $_GET['page'] == 'CiviCRM' ) ? true : false;
}

function wp_get_breadcrumb( ) {
    return;
}

function wp_set_breadcrumb( ) {
    return;
}

function t( $str, $sub = null ) {
    if(is_array($sub))
        $str = str_replace( array_keys($sub), array_values($sub), $str);
    return $str;
}

function civicrm_user_register( $userID ) {
    _civicrm_update_user( $userID );
}

function civicrm_profile_update( $userID ) {
    _civicrm_update_user( $userID );
}

function _civicrm_update_user( $userID ) {
    $user = get_userdata( $userID );
    if ( $user ) {
        civicrm_wp_initialize( );

        require_once 'CRM/Core/BAO/UFMatch.php';
        CRM_Core_BAO_UFMatch::synchronize( $user,
                                           true,
                                           'WordPress',
                                           'Individual' );
    }
}

function civicrm_buffer_start() {
    ob_start( "civicrm_buffer_callback" );
}

function civicrm_buffer_end() {
    ob_end_flush();
}
 
function civicrm_buffer_callback($buffer) {
    // modify buffer here, and then return the updated code
    return $buffer;
}

civicrm_wp_main( );
