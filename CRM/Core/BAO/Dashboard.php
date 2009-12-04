<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

/**
 * Class contains Contact dashboard related functions
 */
require_once 'CRM/Core/DAO/Dashboard.php';

class CRM_Core_BAO_Dashboard extends CRM_Core_DAO_Dashboard
{

    /**
     * Function to get the list of ddashlets 
     * ( defaults dashlets defined by admin )
     *
     *  @param boolean $all all or only active  
     *
     * @return array $widgets  array of dashlets
     * @access public
     * @static
     */
    static function getDashlets( $all = true ) {
        $dashlets = array( );
        $dao = new CRM_Core_DAO_Dashboard( );
        
        if ( !$all ) {
            $dao->is_active = 1;
        }

        $dao->find( );
        $dao->orderBy( 'column_no, weight' );
        while( $dao->fetch( ) ) {
            $values = array( );
            CRM_Core_DAO::storeValues( $dao, $values );
            $dashlets[$dao->id] = $values;
        }
        
        return $dashlets;
    }

    /**
     * Function to get the list of dashlets for a contact
     * and if there are no dashlets for contact return default dashlets and update 
     * contact's preference entry
     *  
     * @param int $contactID contactID
     *
     * @return array $dashlets  array of dashlets
     * @access public
     * @static
     */
    static function getContactDashlets(  ) {
        $dashlets = array( );
        
        $session   = CRM_Core_Session::singleton( );
        $contactID = $session->get('userID');
        
        // get contact dashboard dashlets
        $hasDashlets = false;
        require_once 'CRM/Contact/DAO/DashboardContact.php';
        $dao = new CRM_Contact_DAO_DashboardContact( );
        $dao->contact_id = $contactID;
        $dao->find( );
        $dao->orderBy( 'column_no, weight' );
        while( $dao->fetch( ) ) {
            $hasDashlets = true;
            if ( !$dao->is_active ) {
                continue;
            }
            
            $dashlets[$dao->column_no][$dao->dashboard_id] = $dao->is_minimized;
        }
        
        // if empty then make entry in contact dashboard for this contact
        if ( empty( $dashlets ) && !$hasDashlets ) {
            $defaultDashlets = self::getDashlets( );
            
            //now you need make dashlet entries for logged in contact
            // need to optimize this sql
            foreach ( $defaultDashlets as $key => $values ) {
                $valuesArray[] = " ( {$key}, $contactID )";
            } 
            
            if ( !empty( $defaultDashlets ) ) {
                $valuesString = implode( ',', $valuesArray);
                $query = "
                    INSERT INTO civicrm_dashboard_contact ( dashboard_id, contact_id )
                    VALUES {$valuesString}";

                CRM_Core_DAO::executeQuery( $query );
            }            
        }
         
        return $dashlets;
    }
    
    /**
     * Function to get details of each dashlets
     *
     * @param int $dashletID widget ID
     *
     * @return array associted array title and content
     * @access public
     * @static  
     */
     static function getDashletInfo( $dashletID ) {
         $dashletInfo = array( );
         $dao = new CRM_Core_DAO_Dashboard( );
         
         $dao->id = $dashletID;
         $dao->find( true );

         // if content is empty and url is set, retrieve it from url
         if ( !$dao->content && $dao->url ) {
             $config = CRM_Core_Config::singleton( );
             $url = $config->userFrameworkBaseURL . $dao->url;
             
             //  get content from url
             $dao->content = CRM_Utils_System::getServerResponse( $url );
             $dao->created_date = date( "YmdHis" );
             $dao->save( );
         }
         
         $dashletInfo = array( 'title'   => $dao->label,
                               'content' => $dao->content );
                              
         return $dashletInfo;
     }
     
     /**
      * Function to save changes made by use to the Dashlet
      *
      * @param array $columns associated array
      *
      * @return void
      * @access public
      * @static
      */
      static function saveDashletChanges( $columns ) {
          $session   = CRM_Core_Session::singleton( );
          $contactID = $session->get('userID');
          
          $widgetIDs = array( );
          if ( is_array( $columns ) ) {
              foreach ( $columns as $colNo => $dashlets ) {
                  $weight = 1;
                  foreach ( $dashlets as $widgetID => $isMinimized ) {
                      $isMinimized = (int) $isMinimized;
                      $updateQuery = " UPDATE civicrm_dashboard_contact 
                                        SET weight = {$weight}, is_minimized = {$isMinimized}, column_no = {$colNo}, is_active = 1
                                        WHERE dashboard_id = {$widgetID} AND contact_id = {$contactID} ";
                  
                      // fire update query for each column
                      $dao = CRM_Core_DAO::executeQuery( $updateQuery );
                      
                      $widgetIDs[] = $widgetID;
                      $weight++;
                  }              
              }
          }
          
          if ( !empty( $widgetIDs ) ) {
              // we need to disable widget that removed
              $updateQuery = " UPDATE civicrm_dashboard_contact 
                               SET is_active = 0
                               WHERE dashboard_id NOT IN  ( " . implode( ',', $widgetIDs ). ") AND contact_id = {$contactID}";
          } else {
              // this means all widgets are disabled
              $updateQuery = " UPDATE civicrm_dashboard_contact 
                               SET is_active = 0
                               WHERE contact_id = {$contactID}";
          }
          
          CRM_Core_DAO::executeQuery( $updateQuery );
      }
      
     /**
      * Function to add dashlets
      *  
      * @param array $params associated array
      * 
      * @return object $dashlet returns dashlet object
      * @access public
      * @static
      */
      static function addDashlet( &$params ) {
          require_once "CRM/Core/DAO/Dashboard.php";
          $dashlet  = new CRM_Core_DAO_Dashboard( );
          $dashlet->copyValues( $params );

          $dashlet->created_date = date( "YmdHis" );
          $dashlet->domain_id = CRM_Core_Config::domainID( );
          $dashlet->find( true );
          $dashlet->save( );
          
          // now we need to make dashlet entries for each contact
          self::addContactDashlet( $dashlet );
          
          return $dashlet;
      }
      
      /**
       * Update contact dashboard with new dashlet
       *
       */
      static function addContactDashlet( &$dashlet ) {
          $admin = CRM_Core_Permission::check( 'administer CiviCRM' );
          
          // if dashlet is created by admin then you need to add it all contacts.
          // else just add to contact who is creating this dashlet
          $contactIDs = array( );
          if ( $admin ) {
              $query = "SELECT distinct( contact_id ) 
                        FROM civicrm_dashboard_contact 
                        WHERE contact_id NOT IN ( 
                            SELECT distinct( contact_id ) 
                            FROM civicrm_dashboard_contact WHERE dashboard_id = {$dashlet->id}
                            )";
                                    
              $dao = CRM_Core_DAO::executeQuery( $query );
              while( $dao->fetch( ) ) {
                  $contactIDs[] = $dao->contact_id;
              }
          } else {
              //Get the id of Logged in User
              $session = CRM_Core_Session::singleton( );
              $contactIDs[]  = $session->get( 'userID' );
          }
          
          if ( !empty( $contactIDs ) ) {
              foreach ( $contactIDs as $contactID ) {
                  $valuesArray[] = " ( {$dashlet->id}, {$contactID} )";
              }

              $valuesString = implode( ',', $valuesArray );
              $query = "
                  INSERT INTO civicrm_dashboard_contact ( dashboard_id, contact_id )
                  VALUES {$valuesString}";

              CRM_Core_DAO::executeQuery( $query );
          } 
      }
}