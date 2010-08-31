<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 --------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
   * @copyright CiviCRM LLC (c) 2004-2010
   * $Id$
   *
   */

require_once 'CRM/Core/Page.php';
require_once 'CRM/Core/Permission.php';

/**
 * Page for voting tab interface.
 */
class CRM_Campaign_Page_Vote extends CRM_Core_Page 
{
    
    function reserve( ) 
    {
        //build ajax voter search and selector.
        $controller = new CRM_Core_Controller_Simple( 'CRM_Campaign_Form_Gotv', 'Reserve Voters' );
        $controller->set( 'votingTab',    true );
        $controller->set( 'subVotingTab', 'searchANDReserve' );
        $controller->process( );
        return $controller->run( );
    }
    
    function interview( ) 
    {
        //build interview and release voter interface.
        //$controller = new CRM_Core_Controller_Simple( 'CRM_Campaign_Form_Task_Interview', 'Interview Voters' );
        
        $controller = new CRM_Core_Controller_Simple( 'CRM_Campaign_Form_Gotv', 'Interview Voters' );
        $controller->set( 'votingTab',    true );
        $controller->set( 'subVotingTab', 'searchANDInterview' );
        $controller->process( );
        return $controller->run( );
    }
    
    function browse( ) 
    {   
        $this->_tabs = array( 'reserve'   => ts( 'Reserve Voters' ), 
                              'interview' => ts( 'Interview Voters' ) );
        
        $subPageType = CRM_Utils_Request::retrieve( 'type', 'String', $this );
        if ( $subPageType ) {
            $session = CRM_Core_Session::singleton( ); 
            $session->pushUserContext( CRM_Utils_System::url( 'civicrm/campaign/vote', "reset=1&type={$subPageType}" ) );
            //load the data in tabs.
            $this->{$subPageType}( );
        } else {
            //build the tabs.
            $this->buildTabs( );
        }
        $this->assign( 'subPageType', $subPageType );
        
        //give focus to proper tab.
        $this->assign( 'selectedTabIndex', array_search( CRM_Utils_Array::value( 'subPage', $_GET, 'reserve' ), 
                                                         array_keys( $this->_tabs ) ) ); 
    }
    
    function run( ) 
    {
        $this->browse( );
        
        parent::run();
    }
    
    function buildTabs( ) 
    {        
        $allTabs = array( );
        foreach ( $this->_tabs as $name => $title ) {
            $allTabs[] = array( 'id'    => $name,
                                'title' => $title,
                                'url'   => CRM_Utils_System::url( 'civicrm/campaign/vote', 
                                                                  "reset=1&type=$name&snippet=1" ) );
        }
        
        $this->assign( 'allTabs', $allTabs );
    }
    
}

