<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.1                                                |
 +--------------------------------------------------------------------+
 | Copyright (c) 2005 Social Source Foundation                        |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact the Social Source |
 | Foundation at info[AT]socialsourcefoundation[DOT]org.  If you have |
 | questions about the Affero General Public License or the licensing |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | at http://www.openngo.org/faqs/licensing.html                       |
 +--------------------------------------------------------------------+
*/

/**
 *
 * Given an argument list, invoke the appropriate CRM function
 * Serves as a wrapper between the UserFrameWork and Core CRM
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Social Source Foundation (c) 2005
 * $Id$
 *
 */

require_once 'CRM/Core/I18n.php';
require_once 'CRM/Contact/Controller/Search.php';

class CRM_Core_Invoke {
    
    /**
     * This is the main function that is called on every click action and based on the argument
     * respective functions are called
     *
     * @param $args array this array contains the arguments of the url 
     * 
     * @static
     * @access public
     */    
    static function invoke( $args ) {

        if ( $args[0] !== 'civicrm' ) {
            return;
        }

        $config =& CRM_Core_Config::singleton( );
        if ( $config->userFramework == 'Mambo' ) {
            CRM_Core_Mambo::sidebarLeft( );
        }

        switch ( $args[1] ) {

        case 'contact': return self::contact  ( $args );

        case 'admin'   : return self::admin   ( $args );

        case 'history' : return self::history ( $args );

        case 'group'   : return self::group   ( $args );

        case 'import'  : return self::import  ( $args );
       
        case 'export'  : return self::export  ( $args );

        case 'activity': return self::activity( $args );

        case 'mailing' : return self::mailing ( $args );

        case 'profile' : return self::profile ( $args );
        
        case 'server' :  return self::server  ( $args );

        default        : return CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/contact/search/basic', 'reset=1' ) );

        }
    }

    /**
     * This function contains the actions for arg[1] = contact
     *
     * @param $args array this array contains the arguments of the url 
     *
     * @static
     * @access public
     */
    static function contact( $args ) {
       
        //code added for testing ajax
        if ($args[2] == 'test') {
            $wrapper =& new CRM_Utils_Wrapper( );
            return $wrapper->run( 'CRM_Contact_Form_Test', ts('Test Ajax Page'), $action );
        }

        $session =& CRM_Core_Session::singleton();
        
        $breadCrumbPath = CRM_Utils_System::url( 'civicrm/contact/search/basic', 'force=1' );
       if ($session->get('isAdvanced')) {
           $breadCrumbPath = CRM_Utils_System::url( 'civicrm/contact/search/advanced', 'force=1' );
       }
       
        $additionalBreadCrumb = ts('<a href="%1">Search Results</a>',array(1=>$breadCrumbPath));
       
        
        if ( $args[1] !== 'contact' ) {
            return;
        }

        if ( substr( $args[2], 0, 3 ) == 'add' ) {
            return self::form( CRM_Core_Action::ADD );
        }

        if ( $args[2] == 'email' ) {
            // set the userContext stack
            $session =& CRM_Core_Session::singleton();
            $session->pushUserContext( CRM_Utils_System::url('civicrm/contact/view/activity', 'action=browse' ) );

            $wrapper =& new CRM_Utils_Wrapper( );
            return $wrapper->run( 'CRM_Contact_Form_Task_Email', ts('Email a Contact'),  null );
        }

        if ($args[2] == 'view') {
            CRM_Utils_System::appendBreadCrumb( $additionalBreadCrumb );
            $thirdArg = CRM_Utils_Array::value( 3, $args, '' );
            $fourthArg = CRM_Utils_Array::value(4, $args, 0);
            
            switch ( $thirdArg ) {
            case 'note':
                $view =& new CRM_Contact_Page_View_Note( );
                break;
            case 'rel':
                $view =& new CRM_Contact_Page_View_Relationship( );
                break;
            case 'group':
                $view =& new CRM_Contact_Page_View_GroupContact( );
                break;
            case 'tag':
                $view =& new CRM_Contact_Page_View_Tag( );
                break;
            case 'cd':
                $view =& new CRM_Contact_Page_View_CustomData( );
                break;
            case 'activity':
                $activityId = CRM_Utils_Request::retrieve('activity_id', $form);
                $show = CRM_Utils_Request::retrieve('show', $form);

                $session =& CRM_Core_Session::singleton();
                
                if(!$show) {
                    if ($activityId)  {
                        $session->set('activityId', $activityId);
                    } else {
                        $activityId = $session->get('activityId');
                    }
                }

                if($activityId == 1) {
                    $view =& new CRM_Contact_Page_View_Meeting( );
                } elseif($activityId == 2) {
                    $view =& new CRM_Contact_Page_View_Phonecall( );
                } elseif($activityId == 3) {
                    $details = CRM_Utils_Request::retrieve('details', $form);
                    if ($details) {
                        $view =& new CRM_Contact_Page_View_Email('View Email Details'); 
                    } else {
                        $session->pushUserContext( CRM_Utils_System::url('civicrm/contact/view/activity', 'action=browse' ) );

                        $wrapper =& new CRM_Utils_Wrapper( );
                        return $wrapper->run( 'CRM_Contact_Form_Task_Email', ts('Email a Contact'),  null );
                    }
                } elseif($activityId > 3 ) {
                    $view =& new CRM_Contact_Page_View_OtherActivity( );
                } else {
                    $view =& new CRM_Contact_Page_View_Activity( );
                }
                
                break;
                
            case 'vcard':
                $view =& new CRM_Contact_Page_View_Vcard();
                break;
            default:
                $view =& new CRM_Contact_Page_View_Basic( );
                break;
            }
            return $view->run( );
        }
        
        if ( $args[2] == 'search' ) {
            return self::search( $args );
        }
        
        return CRM_Utils_System::redirect( CRM_Utils_System::url('civicrm/contact/search/basic', 'reset=1', false) );
    }


    /**
     * This function contains the actions for search arguments
     *
     * @param $args array this array contains the arguments of the url 
     *
     * @static
     * @access public
     */
    static function search( $args ) {
        $session =& CRM_Core_Session::singleton( );
        $thirdArg = CRM_Utils_Array::value( 3, $args, '' );

        if ( $thirdArg == 'saved' ) {
            $page =& new CRM_Contact_Page_SavedSearch( );
            return $page->run( );
        }

        if ( $thirdArg == 'advanced' ) {
            // advanced search
            $mode  = CRM_Core_Action::ADVANCED;
            $title = ts('Advanced Search');
            $url   = 'civicrm/contact/search/advanced';
        } else if ( $thirdArg == 'map' ) {
            // set the userContext stack
            $session =& CRM_Core_Session::singleton();
            $session->pushUserContext( CRM_Utils_System::url('civicrm/contact/search/basic' ) );

            $wrapper =& new CRM_Utils_Wrapper( );
            return $wrapper->run( 'CRM_Contact_Form_Task_Map', ts('Map Contact'),  null );
        } else {
            $mode  = CRM_Core_Action::BASIC;
            $title = ts('Search');
            $url   = 'civicrm/contact/search/basic';
        }
        $controller =& new CRM_Contact_Controller_Search($title, $mode);
        $session->pushUserContext(CRM_Utils_System::url($url, 'force=1'));
        return $controller->run();
    }
    
    /**
     * This function contains the default action
     *
     * @param $action 
     *
     * @static
     * @access public
     */
    static function form( $action ) {
        CRM_Utils_System::setUserContext( array( 'civicrm/contact/search/basic', 'civicrm/contact/view' ) );
        $wrapper =& new CRM_Utils_Wrapper( );
        $wrapper->run( 'CRM_Contact_Form_Edit', ts('Contact Page'), $action );
    }
    
    /**
     * This function contains the actions for history arguments
     *
     * @param $args array this array contains the arguments of the url 
     *
     * @static
     * @access public
     */
    static function history( $args ) {
        if ( $args[2] == 'activity' && $args[3] == 'detail' ) {
            $page =& new CRM_History_Page_Activity('View Activity Details');
            return $page->run( );
        }

        if ($args[2] == 'email') {
            $page =& new CRM_History_Page_Email('View Email Details');
            return $page->run( );
        }
    }


    /**
     * This function contains the actions for admin arguments
     *
     * @param $args array this array contains the arguments of the url 
     *
     * @static
     * @access public
     */
    static function admin( $args ) {
        if ( $args[1] !== 'admin' ) {
            return;
        }

        $view = null;
        switch ( CRM_Utils_Array::value( 2, $args, '' ) ) {
        case 'locationType':
            $view =& new CRM_Admin_Page_LocationType(ts('View Location Types'));
            break;
        case 'activityType':
            $view =& new CRM_Admin_Page_ActivityType(ts('View Activity Types'));
            break;
        case 'IMProvider':
            $view =& new CRM_Admin_Page_IMProvider(ts('View Instant Messenger Providers'));
            break;
        case 'mobileProvider':
            $view =& new CRM_Admin_Page_MobileProvider(ts('View Mobile Providers'));
            break;
        case 'reltype':
            $view =& new CRM_Admin_Page_RelationshipType(ts('View Relationship Types'));
            break;
        case 'custom':
            if ( $args[3] == 'group' ) {
                if ( CRM_Utils_Array::value( 4, $args ) != 'field' ) {
                    $view =& new CRM_Custom_Page_Group(ts('Custom Data Group'));
                } else {
                    if ( CRM_Utils_Array::value( 5, $args ) != 'option' ) {
                        $view =& new CRM_Custom_Page_Field(ts('Custom Data Field'));
                    } else {
                        $view =& new CRM_Custom_Page_Option(ts('Custom Data Field'));
                        $url  = CRM_Utils_System::url( 'civicrm/admin/custom/group/field' );
                        $additionalBreadCrumb = '<a href="' . $url . '">' . ts('Custom Data Fields') . '</a>';
                        CRM_Utils_System::appendBreadCrumb( $additionalBreadCrumb );
                    }
                }
            }
            break;
        case 'uf':
            if ( $args[3] == 'group' ) {
                if ( CRM_Utils_Array::value( 4, $args ) != 'field' ) {
                    $view =& new CRM_UF_Page_Group(ts('CiviCRM Profile Group'));
                } else {
                   $view =& new CRM_UF_Page_Field(ts('CiviCRM Profile Field'));
                }
            }
            break;
        case 'commerce':
            if ( $args[3] == 'donation' ) {
                $view =& new CRM_Commerce_Donation_Page_DonationPage(ts('Donation Page'));
            }
            break;
        default:
            $view =& new CRM_Admin_Page_Tag(ts('View Tags'));
            break;
        }
        if ( $view ) {
            return $view->run( );
        }

        return CRM_Utils_System::redirect( CRM_Utils_System::url('civicrm/contact/search/basic', 'reset=1', false) );
    }

    /**
     * This function contains the action for import arguments
     *
     * @params $args array this array contains the arguments of the url 
     *
     * @static
     * @access public
     */
    static function import( $args ) {
        $controller =& new CRM_Import_Controller(ts('Import Contacts'));
        return $controller->run();
    }


    /**
     * This function contains the actions for group arguments
     *
     * @params $args array this array contains the arguments of the url 
     *
     * @static
     * @access public
     */
    static function group( $args ) {
        if ( $args[1] !== 'group' ) {
            return;
        }

        switch ( CRM_Utils_Array::value( 2, $args ) ) {
        case 'add':
            $controller =& new CRM_Group_Controller(ts('Groups'), CRM_Core_Action::ADD);
            $session =& CRM_Core_Session::singleton( );
            $session->pushUserContext(CRM_Utils_System::url('civicrm/group', 'reset=1'));
            return $controller->run();

        case 'search':
            return self::search( $args );

        default:
            $view =& new CRM_Group_Page_Group(ts('View Groups'));
            return $view->run();
        }
    }
    
    /**
     * This function contains the actions for profile arguments
     *
     * @param $args array this array contains the arguments of the url
     *
     * @static
     * @access public
     */
    static function mailing( $args ) {
        if ( $args[1] !== 'mailing' ) {
            return;
        }

        if ( $args[2] == 'component' ) {
            $view =& new CRM_Mailing_Page_Component( );
            return $view->run( );
        }
        if ( $args[2] == 'browse' ) {
            $view =& new CRM_Mailing_Page_Browse( );
            return $view->run( );
        }
        if ( $args[2] == 'event' ) {
            CRM_Utils_System::appendBreadCrumb(
                ts('<a href="%1">Mailings</a>', array(1 =>
                CRM_Utils_System::url('civicrm/mailing/browse'))));
            CRM_Utils_System::appendBreadCrumb(
                ts('<a href="%1">Report</a>', array(1 =>
                CRM_Utils_System::url('civicrm/mailing/report'))));
            $view =& new CRM_Mailing_Page_Event( );
            return $view->run( );
        }
        if ( $args[2] == 'report' ) {
            CRM_Utils_System::appendBreadCrumb(
                ts('<a href="%1">Mailings</a>', array(1 =>
                CRM_Utils_System::url('civicrm/mailing/browse'))));
            $view =& new CRM_Mailing_Page_Report( );
            return $view->run();
        }

        if ( $args[2] == 'send' ) {
            $controller =& new CRM_Mailing_Controller_Send( ts( 'Send Mailing' ) );
            return $controller->run( );
        }

        if ( $args[2] == 'queue' ) {
            CRM_Mailing_BAO_Job::runJobs();
            return;
        }

    }

    /** 
     * This function contains the actions for profile arguments 
     * 
     * @param $args array this array contains the arguments of the url 
     * 
     * @static 
     * @access public 
     */ 
    static function profile( $args ) { 
        if ( $args[1] !== 'profile' ) { 
            return; 
        } 

        $page =& new CRM_Contact_Page_Profile( );
        return $page->run( );
    }
    
    /**
     * handle the export case. this is a hack, so please fix soon
     *
     * @param $args array this array contains the arguments of the url
     *
     * @static
     * @access public
     */
    static function export( $args ) {
        // FIXME:  2005-06-22 15:17:33 by Brian McFee <brmcfee@gmail.com>
        // This function is a dirty, dirty hack.  It should live in its own
        // file.
        $session =& CRM_Core_Session::singleton();
        $type = $_GET['type'];
        
        if ($type == 1) {
            $varName = 'errors';
            $saveFileName = 'Import_Errors.csv';
        } else if ($type == 2) {
            $varName = 'conflicts';
            $saveFileName = 'Import_Conflicts.csv';
        } else if ($type == 3) {
            $varName = 'duplicates';
            $saveFileName = 'Import_Duplicates.csv';
        } else {
            /* FIXME we should have an error here */
            return;
        }
        
        $fileName = $session->get($varName . 'FileName', 
                                    'CRM_Import_Controller');
                                    
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Length: ' . filesize($fileName) );
        header('Content-Disposition: attachment; filename=' . $saveFileName);

        readfile($fileName);
        
        exit();
    }


    /**
     * This function contains the action for server pages (ajax)
     *
     * @params $args array this array contains the arguments of the url 
     *
     * @static
     * @access public
     */
    static function server( $args ) {

        //this code is for state country widget
        if ($args[2] == 'stateCountry') {
            $server =& new CRM_Contact_Page_StateCountryServer( );
            $set = CRM_Utils_Request::retrieve('set', $form);
            if ($set) {
                $path = CRM_Utils_Request::retrieve('path', $form );
               
                $path= '?q='.$path;
                $session =& new CRM_Core_Session();
                $session->set('path', $path);
            }
            return $server->run( $set );
        }

        //this code is for search widget
        if ($args[2] == 'search') {
            $server =& new CRM_Contact_Page_SearchServer( );
            $set = CRM_Utils_Request::retrieve('set', $form);
            if ($set) {
                $path = CRM_Utils_Request::retrieve('path', $form );
               
                $path= '?q='.$path;
                $session =& new CRM_Core_Session();
                $session->set('path', $path);
            }
            return $server->run( $set );
        }

    }
}

?>
