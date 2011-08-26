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


/**
 * Drupal specific stuff goes here
 */
class CRM_Utils_System_Drupal {

    /**
     * sets the title of the page
     *
     * @param string $title
     * @paqram string $pageTitle
     *
     * @return void
     * @access public
     */
    function setTitle( $title, $pageTitle = null ) {
        if ( arg(0) == 'civicrm' )	{
            if ( !$pageTitle ) {
                $pageTitle = $title;
            }

            drupal_set_title( $pageTitle, PASS_THROUGH );
        }
    }
    
    /**
     * Append an additional breadcrumb tag to the existing breadcrumb
     *
     * @param string $title
     * @param string $url   
     *
     * @return void
     * @access public
     * @static
     */
    static function appendBreadCrumb( $breadCrumbs ) {
        $breadCrumb = drupal_get_breadcrumb( );

        if ( is_array( $breadCrumbs ) ) {
            foreach ( $breadCrumbs as $crumbs ) {
                if ( stripos($crumbs['url'], 'id%%') ) {
                    $args = array( 'cid', 'mid' );
                    foreach ( $args as $a ) {
                        $val  = CRM_Utils_Request::retrieve( $a, 'Positive', CRM_Core_DAO::$_nullObject,
                                                             false, null, $_GET );
                        if ( $val ) {
                            $crumbs['url'] = str_ireplace( "%%{$a}%%", $val, $crumbs['url'] );
                        }
                    }
                }
                $breadCrumb[]  = "<a href=\"{$crumbs['url']}\">{$crumbs['title']}</a>";
            }
        }
        drupal_set_breadcrumb( $breadCrumb );
    }

    /**
     * Reset an additional breadcrumb tag to the existing breadcrumb
     *
     * @return void
     * @access public
     * @static
     */
    static function resetBreadCrumb( ) {
        $bc = array( );
        drupal_set_breadcrumb( $bc );
    }

    /**
     * Append a string to the head of the html file
     *
     * @param string $header the new string to be appended
     *
     * @return void
     * @access public
     * @static
     *
     * @todo Not Drupal 7 compatible
     */
    static function addHTMLHead( $header ) {
        if ( ! empty( $header ) ) { 
            drupal_add_html_head($header);
        }
    }

    /** 
     * rewrite various system urls to https 
     *  
     * @param null 
     *
     * @return void 
     * @access public  
     * @static  
     */  
    static function mapConfigToSSL( ) {
        global $base_url;
        $base_url = str_replace( 'http://', 'https://', $base_url );
    }

    /**
     * figure out the post url for the form
     *
     * @param mix $action the default action if one is pre-specified
     *
     * @return string the url to post the form
     * @access public
     * @static
     */
    static function postURL( $action ) {
        if ( ! empty( $action ) ) {
            return $action;
        }

        return self::url( $_GET['q'] );
    }

    /**
     * Generate an internal CiviCRM URL (copied from DRUPAL/includes/common.inc#url)
     *
     * @param $path     string   The path being linked to, such as "civicrm/add"
     * @param $query    string   A query string to append to the link.
     * @param $absolute boolean  Whether to force the output to be an absolute link (beginning with http:).
     *                           Useful for links that will be displayed outside the site, such as in an
     *                           RSS feed.
     * @param $fragment string   A fragment identifier (named anchor) to append to the link.
     * @param $htmlize  boolean  whether to convert to html eqivalant
     * @param $frontend boolean  a gross joomla hack
     *
     * @return string            an HTML string containing a link to the given path.
     * @access public
     *
     */
    function url($path = null, $query = null, $absolute = false,
                 $fragment = null, $htmlize = true,
                 $frontend = false ) {
        $config = CRM_Core_Config::singleton( );
        $script =  'index.php';

        require_once 'CRM/Utils/String.php';
        $path = CRM_Utils_String::stripPathChars( $path );

        if (isset($fragment)) {
            $fragment = '#'. $fragment;
        }

        if ( ! isset( $config->useFrameworkRelativeBase ) ) {
            $base = parse_url( $config->userFrameworkBaseURL );
            $config->useFrameworkRelativeBase = $base['path'];
        }
        $base = $absolute ? $config->userFrameworkBaseURL : $config->useFrameworkRelativeBase;

        $separator = $htmlize ? '&amp;' : '&';

        if (! $config->cleanURL ) {
            if ( isset( $path ) ) {
                if ( isset( $query ) ) {
                    return $base . $script .'?q=' . $path . $separator . $query . $fragment;
                } else {
                    return $base . $script .'?q=' . $path . $fragment;
                }
            } else {
                if ( isset( $query ) ) {
                    return $base . $script .'?'. $query . $fragment;
                } else {
                    return $base . $fragment;
                }
            }
        } else {
            if ( isset( $path ) ) {
                if ( isset( $query ) ) {
                    return $base . $path .'?'. $query . $fragment;
                } else {
                    return $base . $path . $fragment;
                }
            } else {
                if ( isset( $query ) ) {
                    return $base . $script .'?'. $query . $fragment;
                } else {
                    return $base . $fragment;
                }
            }
        }
    }

    /**
     * Authenticate the user against the drupal db
     *
     * @param string $name     the user name
     * @param string $password the password for the above user name
     * @param $loadCMSBootstrap boolean load cms bootstrap?
     *
     * @return mixed false if no auth
     *               array( contactID, ufID, unique string ) if success
     * @access public
     * @static
     */
     static function authenticate( $name, $password, $loadCMSBootstrap = false ) {
        require_once 'DB.php';
        
        $config = CRM_Core_Config::singleton( );
        
        $dbDrupal = DB::connect( $config->userFrameworkDSN );
        if ( DB::isError( $dbDrupal ) ) {
            CRM_Core_Error::fatal( "Cannot connect to drupal db via $config->userFrameworkDSN, " . $dbDrupal->getMessage( ) ); 
        }                                                      

        $account = $userUid = $userMail = null;
        require_once 'CRM/Core/BAO/UFMatch.php';
        
        if ( $loadCMSBootstrap ) {
            $bootStrapParams = array( );
            if ( $name && $password ) {
                $bootStrapParams = array( 'name' => $name,
                                          'pass' => $password ); 
            }
            CRM_Utils_System::loadBootStrap( $bootStrapParams );

            global $user;
            if ( $user ) {
                $userUid = $user->uid;
                $userMail = $user->mail;
            }
        } else {
            // CRM-8638
            // SOAP cannot load drupal bootstrap and hence we do it the old way
            // Contact CiviSMTP folks if we run into issues with this :)
            $cmsPath = self::cmsRootPath( );
            require_once( "$cmsPath/includes/bootstrap.inc" );
            require_once( "$cmsPath/includes/password.inc" );
            
            $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
            $name      = $dbDrupal->escapeSimple( $strtolower( $name ) );
            $sql = "
SELECT u.* 
FROM   {$config->userFrameworkUsersTableName} u
WHERE  LOWER(u.name) = '$name'
AND    u.status = 1
";
            
            $query = $dbDrupal->query( $sql );
            $row = $query->fetchRow( DB_FETCHMODE_ASSOC );
            
            if ( $row ) {
               $fakeDrupalAccount = drupal_anonymous_user();
               $fakeDrupalAccount->name = $name;
               $fakeDrupalAccount->pass = $row['pass'];
               $passwordCheck = user_check_password($password, $fakeDrupalAccount);
               if ( $passwordCheck ) {
                   $userUid = $row['uid']; 
                   $userMail = $row['mail'];
               }
            }
        }
        
        if ( $userUid && $userMail ) { 
            CRM_Core_BAO_UFMatch::synchronizeUFMatch( $account, $userUid , $userMail, 'Drupal' );
            $contactID = CRM_Core_BAO_UFMatch::getContactId( $userUid );
            if ( ! $contactID ) {
                return false;
            }
            return array( $contactID, $userUid, mt_rand() );
        }
        return false;
    }

    /**   
     * Set a message in the UF to display to a user 
     *   
     * @param string $message the message to set 
     *   
     * @access public   
     * @static   
     */   
    static function setMessage( $message ) {
        drupal_set_message( $message );
    }

    static function permissionDenied( ) {
        drupal_access_denied( );
    }

    static function logout( ) {
        module_load_include( 'inc', 'user', 'user.pages' );
        return user_logout( );
    }

    static function updateCategories( ) {
        // copied this from profile.module. Seems a bit inefficient, but i dont know a better way
        // CRM-3600
        cache_clear_all();
        menu_rebuild();
    }

    /**
     * Get the locale set in the hosting CMS
     * @return string  with the locale or null for none
     */
    static function getUFLocale()
    {
        // return CiviCRM’s xx_YY locale that either matches Drupal’s Chinese locale
        // (for CRM-6281), Drupal’s xx_YY or is retrieved based on Drupal’s xx
        global $language;
        switch (true) {
        case $language->language == 'zh-hans':             return 'zh_CN';
        case $language->language == 'zh-hant':             return 'zh_TW';
        case preg_match('/^.._..$/', $language->language): return $language->language;
        default:
            require_once 'CRM/Core/I18n/PseudoConstant.php';
            return CRM_Core_I18n_PseudoConstant::longForShort(substr($language->language, 0, 2));
        }
    }

    /**
     * load drupal bootstrap
     *
     * @param $params array with uid or name and password 
     * @param $loadUser boolean load cms user?
     * @param $throwError throw error on failure?
     */
    static function loadBootStrap( $params = array( ), $loadUser = true, $throwError = true )
    {
        //take the cms root path.
        $cmsPath = self::cmsRootPath( );
        
        if ( !file_exists( "$cmsPath/includes/bootstrap.inc" ) ) {
            if ( $throwError ) {
                echo '<br />Sorry, could not able to locate bootstrap.inc.';
                exit( );
            }
            return false;
        }
        
        // load drupal bootstrap
        chdir($cmsPath);
        define('DRUPAL_ROOT', $cmsPath);
        require_once 'includes/bootstrap.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        
        // explicitly setting error reporting, since we cannot handle drupal related notices
        error_reporting( 1 );
        if ( !function_exists('module_exists') || 
             !module_exists( 'civicrm' ) ) {
            if ( $throwError ) {
                echo '<br />Sorry, could not able to load drupal bootstrap.';
                exit( );
            } 
            return false;
        }
        
        if ( !$loadUser ) {
            return true;
        }
      
        $uid = CRM_Utils_Array::value( 'uid', $params );
        if ( !$uid ) {
            //load user, we need to check drupal permissions.
            $name = CRM_Utils_Array::value( 'name', $params, false ) ? $params['name'] : trim(CRM_Utils_Array::value('name', $_REQUEST));
            $pass = CRM_Utils_Array::value( 'pass', $params, false ) ? $params['pass'] : trim(CRM_Utils_Array::value('pass', $_REQUEST));

            if ( $name ) {
                $uid = user_authenticate( $name,  $pass );
                if ( !$uid ) {
                    if ( $throwError ) {
                        echo '<br />Sorry, unrecognized username or password.';
                        exit( );
                    }
                    return false;
                }
            } 
        }
        
        if ( $uid ) {
            $account = user_load($uid );
            if ( $account && $account->uid ) {
                global $user;
                $user = $account;
                return true;
            }
        }
        
        if ( $throwError ) {
            echo '<br />Sorry, can not load CMS user account.';
            exit( );
        }
        
        // CRM-6948: When using loadBootStrap, it's implicit that CiviCRM has already loaded its settings, which means that define(CIVICRM_CLEANURL) was correctly set.
        // So we correct it
        $config = CRM_Core_Config::singleton();
        $config->cleanURL = (int)variable_get('clean_url', '0'); 
        
        // CRM-8655: Drupal wasn't available during bootstrap, so hook_civicrm_config never executes
        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::config( $config );

        return false;
    }
    
    static function cmsRootPath( ) 
    {
        $cmsRoot = $valid = null;
        
        $path = $_SERVER['SCRIPT_FILENAME'];
        if ( function_exists( 'drush_get_context' ) ) {
            // drush anyway takes care of multisite install etc
            return drush_get_context('DRUSH_DRUPAL_ROOT');
        }
        // CRM-7582
        $pathVars = explode( '/', 
                             str_replace('//', '/', 
                                         str_replace( '\\', '/', $path ) ) );
        
        //lets store first var,
        //need to get back for windows.
        $firstVar = array_shift( $pathVars );
        
        //lets remove sript name to reduce one iteration.
        array_pop( $pathVars );
        
        //CRM-7429 --do check for upper most 'includes' dir,
        //which would effectually work for multisite installation.
        do {
            $cmsRoot = $firstVar . '/'.implode( '/', $pathVars );
            $cmsIncludePath = "$cmsRoot/includes";
            //stop as we found bootstrap.
            if ( @opendir( $cmsIncludePath ) && 
                 file_exists( "$cmsIncludePath/bootstrap.inc" ) ) { 
                $valid = true;
                break;
            }
            //remove one directory level.
            array_pop( $pathVars );
        } while ( count( $pathVars ) ); 
        
        return ( $valid ) ? $cmsRoot : null;
    }
    
    /**
     * check is user logged in.
     *
     * @return boolean true/false.
     */
    public static function isUserLoggedIn( ) {
        $isloggedIn = false;
        if ( function_exists( 'user_is_logged_in' ) ) {
            $isloggedIn = user_is_logged_in( );
        }
        
        return $isloggedIn;
    }
    
    /**
     * Get currently logged in user uf id.
     *
     * @return int $userID logged in user uf id.
     */
    public static function getLoggedInUfID( ) {
        $ufID = null;
        if ( function_exists( 'user_is_logged_in' ) && 
             user_is_logged_in( ) && 
             function_exists( 'user_uid_optional_to_arg' ) ) {
            $ufID = user_uid_optional_to_arg( array( ) );
        }
        
        return $ufID;
    }
    
}
