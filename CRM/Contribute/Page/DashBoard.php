<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Core/Page.php';

/**
 * Page for displaying list of Payment-Instrument
 */
class CRM_Contribute_Page_DashBoard extends CRM_Core_Page 
{
    /** 
     * Heart of the viewing process. The runner gets all the meta data for 
     * the contact and calls the appropriate type of page to view. 
     * 
     * @return void 
     * @access public 
     * 
     */ 
    function preProcess( ) 
    {
        $startToDate = array( );
        $yearToDate  = array( );
        $monthToDate = array( );

        $status = array( 'Valid', 'Cancelled' );
        
        $startDate = null;
        $config =& CRM_Core_Config::singleton( );
        $yearDate = $config->fiscalYearStart;
        $year  = array('Y' => date('Y'));
        $yearDate = array_merge($year,$yearDate);
        $yearDate = CRM_Utils_Date::format( $yearDate );
  
        $monthDate = date('Ym') . '01000000';

        $prefixes = array( 'start', 'month', 'year'  );
        $status   = array( 'Valid', 'Cancelled' );
       
        $yearNow = $yearDate + 10000;
        $yearNow .= '000000';
        $yearDate  = $yearDate  . '000000';
        
        // we are specific since we want all information till this second
        $now       = date( 'YmdHis' );
       
        require_once 'CRM/Contribute/BAO/Contribution.php';
        foreach ( $prefixes as $prefix ) {
            $aName = $prefix . 'ToDate';
            $dName = $prefix . 'Date';
            
            if ( $prefix == 'year') {
                $now  = $yearNow;
            }
            foreach ( $status as $s ) {

                ${$aName}[$s]        = CRM_Contribute_BAO_Contribution::getTotalAmountAndCount( $s, $$dName, $now );
                ${$aName}[$s]['url'] = CRM_Utils_System::url( 'civicrm/contribute/search',
                                                              "reset=1&force=1&status=1&start={$$dName}&end=$now&test=0");
            }
            $this->assign( $aName, $$aName );
        }
    }

    /** 
     * This function is the main function that is called when the page loads, 
     * it decides the which action has to be taken for the page. 
     *                                                          
     * return null        
     * @access public 
     */                                                          
    function run( ) { 
        $this->preProcess( );
        
        $controller =& new CRM_Core_Controller_Simple( 'CRM_Contribute_Form_Search', ts('Contributions'), null );
        $controller->setEmbedded( true ); 
        $controller->reset( ); 
        $controller->set( 'limit', 10 );
        $controller->set( 'force', 1 );
        $controller->set( 'context', 'dashboard' ); 
        $controller->process( ); 
        $controller->run( ); 
        
        return parent::run( );
    }

}

?>
